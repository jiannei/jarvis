<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class Eleduck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:eleduck';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「电鸭」';

    /**
     * Execute the console command.
     */
    public function handle(CrawlerService $service): void
    {
        Auth::loginUsingId(1);

        $posts = $service->handleEleDuck();

        foreach ($posts['blogs'] as $item) {
            $this->comment('正在获取：'.$item['title']);

            $post = $service->handleJspangPost($item['link']);

            CrawlFinished::dispatch($post, 'eleduck', 'html');
        }

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));

        Auth::logout();
    }
}
