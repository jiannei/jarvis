<?php

namespace App\Console\Commands;

use App\Models\Mongodb\GithubTrendingDaily;
use App\Services\CrawlerService;
use Illuminate\Console\Command;

class GithubTrending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:trending {--language=} {--spoken_language_code=zh} {--since=daily}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬取 Github Trending';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CrawlerService $crawlerService)
    {
        $language = $this->option('language');
        $spokenLanguageCode = $this->option('spoken_language_code');
        $since = $this->option('since');

        $trendings = $crawlerService->handleGithubTrending($language, [
            'spoken_language_code' => $spokenLanguageCode,
            'since' => $since,
        ]);

        foreach ($trendings as $trending) {
            $trending['day'] = now()->format('Y-m-d');

            GithubTrendingDaily::updateOrCreate([
                'day' =>$trending['day'],
                'repo' => $trending['repo']
            ], $trending);
        }

        return self::SUCCESS;
    }
}
