<?php

namespace App\Console\Commands\Crawl;

use App\Enums\CrawlEnum;
use App\Services\CrawlerService;
use Illuminate\Console\Command;

class LaravelNewsBlog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:laravel-news:blog {mode=default} {--link=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬取 laravel-news blog';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CrawlerService $service)
    {
        $this->info("[{$this->description}]:执行开始");

        if ($this->argument('mode') === 'all') {
            $blogs = $service->handleLaravelNewsBlogs();
            $links = array_column($blogs,'link');

            $bar = $this->output->createProgressBar(count($links));
            $bar->start();

            foreach ($links as $link) {
                $this->newLine();
                $this->comment('正在爬取:'.CrawlEnum::LARAVEL_NEWS.$link);
                $service->handleLaravelNewsBlog(trim($link,'/'));
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }else{
            $link = $this->option('link') ?: '';

            if ($link) {
                $this->comment('正在爬取:'.CrawlEnum::LARAVEL_NEWS.$link);
                $service->handleLaravelNewsBlog(trim($this->option('link'),'/'));
            }
        }

        $this->info("[{$this->description}]:执行结束");

        return self::SUCCESS;
    }
}
