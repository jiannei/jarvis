<?php

namespace App\Console\Commands\Github;

use App\Jobs\CrawlGithubTrendingSpokenLanguage;
use Illuminate\Console\Command;

class TrendingSpokenLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:trending:spoken-language';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬取 Github Trending Spoken Language';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        CrawlGithubTrendingSpokenLanguage::dispatch();

        return self::SUCCESS;
    }
}
