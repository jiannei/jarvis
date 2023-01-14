<?php

namespace App\Console\Commands;

use App\Jobs\CrawlGithubTrending;
use Illuminate\Console\Command;

class GithubTrending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'github:trending {mode=all} {--language=} {--spoken_language_code=zh} {--since=daily}';

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
    public function handle()
    {
        $language = $this->option('language');
        $spokenLanguageCode = $this->option('spoken_language_code');
        $since = $this->option('since');

        $this->info("[{$this->description}]:执行开始");
        if ($this->argument('mode') === 'all') {
            $bar = $this->output->createProgressBar(count($this->params()));
            $bar->start();

            foreach ($this->params() as $param) {
                [$language, $spokenLanguageCode, $since] = array_values($param);

                dispatch(new CrawlGithubTrending($language, $spokenLanguageCode, $since));

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        } else {
            dispatch(new CrawlGithubTrending($language, $spokenLanguageCode, $since));
        }

        $this->info("[{$this->description}]:执行结束");

        return self::SUCCESS;
    }

    private function params(): array
    {
        return [
            ['language' => null, 'spoken_language_code' => 'zh', 'since' => 'daily'],
            ['language' => 'php', 'spoken_language_code' => 'zh', 'since' => 'daily'],
            ['language' => 'golang', 'spoken_language_code' => 'zh', 'since' => 'daily'],
            ['language' => 'javascript', 'spoken_language_code' => 'zh', 'since' => 'daily'],
            ['language' => 'vue', 'spoken_language_code' => 'zh', 'since' => 'daily'],
        ];
    }
}
