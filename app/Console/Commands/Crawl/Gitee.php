<?php

namespace App\Console\Commands\Crawl;

use App\Jobs\CrawlGiteeExplore;
use App\Jobs\CrawlGiteeTrending;
use App\Models\Gitee\TrendingDaily;
use App\Models\Gitee\TrendingWeekly;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class Gitee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:gitee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「gitee」推荐项目';

    /**
     * Execute the console command.
     */
    public function handle(CrawlerService $service): void
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $result = $service->handleGitee();

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

        //        dd($result->get('repos'));

        //        dispatch(new CrawlGiteeTrending());

        $bar = $this->output->createProgressBar(count($result->get('languages')));
        $bar->start();

        $result->get('languages')->each(function ($language) use ($bar) {
            $this->comment(' '.$language['link']);

            dispatch(new CrawlGiteeExplore($language['link']));

            $bar->advance();
        });

        $bar->finish();
        $this->newLine();

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
