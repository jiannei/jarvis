<?php

namespace App\Jobs;

use App\Models\Github\TrendingLanguage;
use App\Services\CrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrawlGithubTrendingLanguage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CrawlerService $service)
    {
        $languages = $service->handleGithubTrendingLanguages();

        foreach ($languages as $language) {
            TrendingLanguage::updateOrCreate(['code' => $language['code']], $language);
        }
    }
}
