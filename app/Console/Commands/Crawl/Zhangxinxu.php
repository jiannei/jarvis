<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use App\Services\RssService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Zhangxinxu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:zxx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「张鑫旭博客」';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(RssService $service)
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $blogs = $service->handleZhangXinxuBlog();

        foreach ($blogs['items'] as $blog) {
            $this->comment('正在获取：'.$blog['title']);

            $topic = [
                'title' => $blog['title'],
                'link' => $blog['link'],
                'description' => $blog['description'],
                'author' => [
                    'name' => '张鑫旭',
                ],
                'category' => [
                    'name' => $blog['category'],
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($blog['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'zhangxinxu', 'html');
        }

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));

        Auth::logout();

        return self::SUCCESS;
    }
}
