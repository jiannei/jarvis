<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\RssService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Airing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:airing-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「AIRING WEEKLY」';

    /**
     * Execute the console command.
     */
    public function handle(RssService $service): void
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $posts = $service->handleAiringWeekly();

        foreach ($posts['items'] as $post) {
            $this->comment('正在获取：'.$post['title']);

            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'airing',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'weekly',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'airing', 'html');
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
