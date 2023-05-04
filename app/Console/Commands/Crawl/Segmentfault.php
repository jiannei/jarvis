<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class Segmentfault extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:segmentfault';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「segmentfault」';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        foreach ($this->channels() as $channel) {
            $this->crawl($channel);
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }

    private function channels()
    {
        return [
            'frontend', 'backend', 'miniprogram', 'toolkit',
        ];
    }

    private function crawl($channel)
    {
        $posts = Crawler::rss('https://rsshub.app/segmentfault/channel/'.$channel);

        foreach ($posts['items'] as $post) {
            $this->comment('正在获取：'.$post['title']);

            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'segmentfault',
                ],
                'category' => [
                    'name' => $posts->get('channel')['title'] ?? 'segmentfault',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'segmentfault');
        }
    }
}
