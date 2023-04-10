<?php

namespace App\Jobs;

use App\Models\Gitee\Explore;
use App\Services\CrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class CrawlGiteeExplore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $language)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CrawlerService $service): void
    {
       $result = $service->handleGitee($this->language)->only(['repos']);

       dump($result['repos']);

        foreach ($result['repos'] as $item) {
           $data = [
               'repo' => $item['repo']['link'],
               'stars' => $item['repo']['stars'],
               'desc' => $item['repo']['desc'],
               'language' => $item['language'],
               'category' => $item['category'],
               'latest_updated_at' => Carbon::createFromTimestamp(strtotime($item['updated_at']))->format('Y-m-d H:i:s'),
           ];

            Explore::updateOrCreate([
                'repo' => $item['repo']['link'],
            ], $data);
        }
    }
}
