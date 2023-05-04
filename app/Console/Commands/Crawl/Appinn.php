<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\RssService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class Appinn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:appinn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「小众软件」';

    /**
     * Execute the console command.
     */
    public function handle(RssService $service): void
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::onceUsingId(1);

        $posts = Crawler::rss('https://feeds.appinn.com/appinns/', [
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
                    'description' => ['content\:encoded', 'text'],
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
                    'name' => 'appinn',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'daily',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'appinn', 'html');
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
