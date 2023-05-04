<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class LearnKu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:learnku {community=laravel}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「LearnKu」';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CrawlerService $service)
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::onceUsingId(1);

        $data = $service->handleLearnKu($this->argument('community'));

        foreach ($data as $item) {
            $this->comment('正在获取：'.$item['title']);

            $topic = $service->handleLearnKuTopic($item['link']);

            CrawlFinished::dispatch($topic, 'learnku', 'html');
        }

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));

        return self::SUCCESS;
    }
}
