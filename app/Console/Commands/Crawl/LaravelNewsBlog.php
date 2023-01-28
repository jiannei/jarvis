<?php

namespace App\Console\Commands\Crawl;

use App\Enums\CrawlEnum;
use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

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
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        if ($this->argument('mode') === 'all') {
            $blogs = $service->handleLaravelNewsBlogs();
            $links = array_column($blogs, 'link');

            $bar = $this->output->createProgressBar(count($links));
            $bar->start();

            foreach ($links as $link) {
                $this->newLine();
                $this->comment('正在爬取:'.CrawlEnum::LARAVEL_NEWS.$link);

                $post = $service->handleLaravelNewsBlog(trim($link, '/'));
                CrawlFinished::dispatch($post, 'laravel-news'); // todo job or event?

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        } else {
            $link = $this->option('link') ?: '';

            if ($link) {
                $this->comment('正在爬取:'.CrawlEnum::LARAVEL_NEWS.$link);

                $post = $service->handleLaravelNewsBlog(trim($this->option('link'), '/'));
                CrawlFinished::dispatch($post, 'laravel-news');
            }
        }

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));

        return self::SUCCESS;
    }
}
