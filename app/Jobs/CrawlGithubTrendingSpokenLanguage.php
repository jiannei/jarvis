<?php

namespace App\Jobs;

use App\Models\Github\TrendingSpokenLanguage;
use App\Services\CrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrawlGithubTrendingSpokenLanguage implements ShouldQueue
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
        $spokenLanguages = $service->handleGithubTrendingSpokenLanguages();

        foreach ($spokenLanguages as $spokenLanguage) {
            TrendingSpokenLanguage::updateOrCreate(['code' => $spokenLanguage['code']],$spokenLanguage);
        }
    }
}
