<?php

namespace App\Console\Commands\Crawl\Github;

use App\Jobs\CrawlGithubTrendingLanguage;
use Illuminate\Console\Command;

class TrendingLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:github:trending:language';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「Github Trending Language」';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        CrawlGithubTrendingLanguage::dispatch();

        return self::SUCCESS;
    }
}
