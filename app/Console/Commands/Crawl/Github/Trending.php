<?php

namespace App\Console\Commands\Crawl\Github;

use App\Jobs\CrawlGithubTrending;
use Illuminate\Console\Command;

class Trending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:github:trending {mode=default} {--language=} {--spoken_language_code=zh} {--since=daily}';

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
        if ($this->argument('mode') === 'default') {
            CrawlGithubTrending::dispatch($language, $spokenLanguageCode, $since);
        } else {
            $bar = $this->output->createProgressBar(count($this->params()));
            $bar->start();

            foreach ($this->params() as $param) {
                [$language, $spokenLanguageCode, $since] = array_values($param);

                CrawlGithubTrending::dispatch($language, $spokenLanguageCode, $since);

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
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
