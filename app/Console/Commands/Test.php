<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '消费爬取数据';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("[{$this->description}]:开始 ".now()->format('Y-m-d H:i:s'));

        $posts = Crawler::json('leetcode-cn');

        dd($posts);

        $this->info("[{$this->description}]:结束 ".now()->format('Y-m-d H:i:s'));
    }
}
