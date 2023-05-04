<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class Ifanr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:ifanr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「爱范儿」资讯';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $posts = Crawler::rss('https://www.ifanr.com/feed', [
            [
                'alias' => 'channel',
                'selector' => 'channel',
                'rules' => [
                    'title' => ['title', 'text'],
                    'link' => ['link', 'text'],
                    'description' => ['description', 'text'],
                    'pubDate' => ['pubDate', 'text'],
                ],
            ],
            [
                'alias' => 'items',
                'selector' => 'channel item',
                'rules' => [
                    'category' => ['category', 'text'],
                    'title' => ['title', 'text'],
                    'description' => ['description', 'text'],
                    'link' => ['link', 'text'],
                    'author' => ["dc\:creator", 'text'],
                    'guid' => ['guid', 'text'],
                    'pubDate' => ['pubDate', 'text'],
                ],
            ],
        ]);

        foreach ($posts['items'] as $post) {
            $this->comment('正在获取：'.$post['title']);

            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'ifanr',
                ],
                'category' => [
                    'name' => $posts->get('channel')['title'] ?? 'ifanr',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'ifanr');
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
