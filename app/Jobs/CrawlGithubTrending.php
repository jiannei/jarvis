<?php

namespace App\Jobs;

use App\Models\Github\TrendingDaily;
use App\Models\Github\TrendingMonthly;
use App\Models\Github\TrendingWeekly;
use App\Services\CrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

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
            $item['spoken_language_code'] = $this->spokenLanguageCode;
            $item['stars'] = Str::remove(',', $item['stars']);
            $item['forks'] = Str::remove(',', $item['forks']);
            $item['added_stars'] = Str::remove([
                ',',
                ' star today', ' stars today',
                ' star this week', 'stars this week',
                ' star this month', 'stars this month',
            ], $item['added_stars']);

            if ($this->since === 'monthly') {
                $item['month'] = now()->format('Y-m');

                TrendingMonthly::updateOrCreate([
                    'month' => $item['month'],
                    'repo' => $item['repo'],
                ], $item);
            } elseif ($this->since === 'weekly') {
                $item['week'] = now()->format('Y-W');

                TrendingWeekly::updateOrCreate([
                    'week' => $item['week'],
                    'repo' => $item['repo'],
                ], $item);
            } else {
                $item['day'] = now()->format('Y-z');

                TrendingDaily::updateOrCreate([
                    'day' => $item['day'],
                    'repo' => $item['repo'],
                ], $item);
            }
        }
    }
}
