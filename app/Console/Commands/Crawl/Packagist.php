<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\RssService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Packagist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:packagist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「packagist」';

    /**
     * Execute the console command.
     */
    public function handle(RssService $service): void
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::onceUsingId(1);

        // TODO 生成 daily?
        $posts = $service->handlePackagist();

        foreach ($posts['items'] as $post) {
            $this->comment('正在获取：'.$post['title']);

            $topic = [
                'title' => 'packagist: '.$post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'packagist',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'daily',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'packagist', 'html');
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
