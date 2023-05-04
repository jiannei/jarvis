<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\RssService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DecoHack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:decohack';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「DechoHack 产品周刊」';

    /**
     * Execute the console command.
     */
    public function handle(RssService $service): void
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $posts = $service->handleViggoDecoHack();

        foreach ($posts['items'] as $post) {
            $this->comment('正在获取：'.$post['title']);

            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'viggo',
                ],
                'category' => [
                    'name' => $post['category'],
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'decohack', 'html');
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
