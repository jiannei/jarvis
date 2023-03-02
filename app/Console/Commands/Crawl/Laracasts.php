<?php

namespace App\Console\Commands\Crawl;

use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class Laracasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:laracasts {menu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「laracasts」';

    /**
     * Execute the console command.
     */
    public function handle(CrawlerService $service): void
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::onceUsingId(1);

        $posts = $service->handleLaracasts($this->argument('menu'));


        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
