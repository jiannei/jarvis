<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class Jspang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:jspang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「jspang」博客';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CrawlerService $service)
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $posts = $service->handleJspang();

        foreach ($posts['blogs'] as $item) {
            $this->comment('正在获取：'.$item['title']);

            $post = $service->handleJspangPost($item['link']);

            CrawlFinished::dispatch($post, 'jspang', 'html');
        }

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));

        Auth::logout();

        return self::SUCCESS;
    }
}
