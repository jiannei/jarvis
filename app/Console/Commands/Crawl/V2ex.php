<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class V2ex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:v2ex {--tab=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「v2ex」';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CrawlerService $service)
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $data = $service->handleV2ex($this->option('tab'));

//        try {
        foreach ($data['posts'] ?? [] as $item) {
            $this->comment('正在获取：'.$item['title']);

            $topicId = explode('#', explode('/', $item['link'])[2])[0];
            $topic = $service->handleV2exTopic($topicId);

            CrawlFinished::dispatch($topic, 'v2ex', 'markdown');
        }
//        } catch (\Throwable $e) {
//            $this->error("[{$this->description}]:执行异常 ".$e->getMessage());
//        }

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));

        return self::SUCCESS;
    }
}
