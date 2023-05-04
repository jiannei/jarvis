<?php

namespace App\Console\Commands\Crawl;

use App\Events\CrawlFinished;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class Cnblogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:cnblogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「博客园」资讯';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        foreach ($this->routes() as $route) {
            $this->crawl($route);
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }

    private function routes()
    {
        return [
            '/cnblogs/aggsite/topdiggs', '/cnblogs/aggsite/topviews', '/cnblogs/aggsite/headline',
            '/cnblogs/cate/go', '/cnblogs/cate/php', '/cnblogs/cate/vue', '/cnblogs/cate/javascript', '/cnblogs/cate/react',
            '/cnblogs/pick',
        ];
    }

    private function crawl($route)
    {
        $posts = Crawler::rss('https://rsshub.app'.$route);

        foreach ($posts['items'] as $post) {
            $this->comment('正在获取：'.$route.'-'.$post['title']);

            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'cnblogs',
                ],
                'category' => [
                    'name' => $posts->get('channel')['title'] ?? 'cnblogs',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'cnblogs');
        }
    }
}
