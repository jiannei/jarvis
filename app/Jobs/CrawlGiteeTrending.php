<?php

namespace App\Jobs;

use App\Models\Gitee\TrendingDaily;
use App\Models\Gitee\TrendingWeekly;
use App\Services\CrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrawlGiteeTrending implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CrawlerService $service): void
    {
       $result = $service->handleGitee()->only(['daily', 'weekly']);

        foreach ($result['daily'] as $item) {
            $item['day'] = now()->format('Y-z');
            $item['language'] = '';

            TrendingDaily::updateOrCreate([
                'day' => $item['day'],
                'repo' => $item['repo'],
            ], $item);
        }

        foreach ($result['weekly'] as $item) {
            $item['week'] = now()->format('Y-W');
            $item['language'] = '';

            TrendingWeekly::updateOrCreate([
                'week' => $item['week'],
                'repo' => $item['repo'],
            ], $item);
        }
    }
}
