<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;

class RuanyfWeekly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:ruanyf:weekly {mode=latest}';

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
        $latest = $service->handleRuanyfWeekly();

        CrawlFinished::dispatch($latest, 'laravel-news', 'markdown');

        return self::SUCCESS;
    }
}
