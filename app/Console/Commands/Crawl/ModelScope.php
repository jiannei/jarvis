<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class ModelScope extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:model-scope';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「ModelScope」魔搭社区';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $posts = Crawler::rss('https://rsshub.app/modelscope/datasets');

        foreach ($posts['items'] as $post) {
            $title = $post['title'] ?? '';
            if (!$title) {
                continue;
            }

            $this->comment('正在获取：'.$title);

            $topic = [
                'title' => $title,
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'ModelScope',
                ],
                'category' => [
                    'name' => $posts->get('channel')['title'] ?? 'ModelScope',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'ModelScope');
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
