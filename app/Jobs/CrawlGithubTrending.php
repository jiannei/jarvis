<?php

namespace App\Jobs;

use App\Models\Mongodb\GithubTrendingDaily;
use App\Services\CrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrawlGithubTrending implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?string $language;

    private ?string $spokenLanguageCode;

    private string $since;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(?string $language = null, ?string $spokenLanguageCode = null, string $since = 'daily')
    {
        $this->language = $language;
        $this->spokenLanguageCode = $spokenLanguageCode;
        $this->since = $since;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CrawlerService $service)
    {
        $trending = $service->handleGithubTrending($this->language, [
            'spoken_language_code' => $this->spokenLanguageCode,
            'since' => $this->since,
        ]);

        foreach ($trending as $item) {
            $item['day'] = now()->format('Y-m-d');

            GithubTrendingDaily::updateOrCreate([
                'day' => $item['day'],
                'repo' => $item['repo'],
            ], $item);
        }
    }
}