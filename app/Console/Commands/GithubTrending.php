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
    protected $signature = 'app:crawl:github:trending {mode=default} {--language=} {--spoken_language_code=} {--since=daily}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「Github Trending」';

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

        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        if ($this->argument('mode') === 'default') {
            CrawlGithubTrending::dispatch($language, $spokenLanguageCode, $since);
        } else {
            $bar = $this->output->createProgressBar(count($this->languages()));
            $bar->start();

            foreach ($this->languages() as $item) {
                CrawlGithubTrending::dispatch($item, $spokenLanguageCode, $since);

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));

        return self::SUCCESS;
    }

    private function languages(): array
    {
        return [
            null, 'php', 'golang', 'javascript', 'vue', 'go', 'typescript', 'java', 'python',
        ];
    }
}
