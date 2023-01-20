<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class RuanyfWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:ruanyf:weekly {mode=latest} {--period=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬取阮一峰科技爱好者周刊';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CrawlerService $service)
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $latest = $service->handleRuanyfWeeklyLatest((int) $this->option('period'));

        CrawlFinished::dispatch($latest, 'github', 'markdown');

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));

        return self::SUCCESS;
    }
}
