<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class Cxy521 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:cxy521 {--page=index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬取「cxy521」导航';

    /**
     * Execute the console command.
     */
    public function handle(CrawlerService $service): void
    {
        $pages = [
            'index' => '链接',
            'manual' => '手册',
            'book'=> '书籍',
        ];

        if (!isset($pages[$this->option('page')])) {
            $this->error('参数错误');
            return;
        }

        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        $result = $service->handleCxy521($this->option('page'));

        foreach ($result as $item) {
            $this->comment('正在获取：'.$item['category']);

            foreach ($item['links'] as $link) {
                $topic = [
                    'title' => $item['category'],
                    'link' => $link['link'],
                    'description' => $link['description'] ?? '',
                    'author' => [
                        'name' => 'cxy521',
                    ],
                    'category' => [
                        'name' => $pages[$this->option('page')],
                    ],
                    'publishDate' => now(),
                ];

                CrawlFinished::dispatch($topic, 'cxy521', 'html');
            }
        }

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
