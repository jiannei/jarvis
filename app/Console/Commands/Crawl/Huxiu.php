<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class Huxiu extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:huxiu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「虎嗅」资讯';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $posts = Crawler::rss('https://rss.huxiu.com/');

        foreach ($posts['items'] as $post) {
            $this->comment('正在获取：'.$post['title']);

            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'huxiu',
                ],
                'category' => [
                    'name' => $posts->get('channel')['title'] ?? 'huxiu',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'huxiu');
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
