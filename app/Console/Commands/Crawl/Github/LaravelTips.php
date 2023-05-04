<?php

namespace App\Console\Commands\Crawl\Github;

use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class LaravelTips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:github:laravel-tips';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「laravel-tips」';

    /**
     * Execute the console command.
     */
    public function handle(CrawlerService $service): void
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $owner = 'LaravelDaily';
        $repo = 'laravel-tips';
        $branch = 'master';

        $tips = $service->handleLaravelTips($owner, $repo, $branch);

        foreach ($tips as $tip) {
            $category = trim($tip['path'], '.md');

            $this->comment('正在获取：'.$category);

            $post = [
                'title' => 'laravel-tips:'.$category,
                'link' => "https://github.com/{$owner}/{$repo}/blob/{$branch}/{$tip['path']}",
                'description' => $tip['content'],
                'author' => [
                    'name' => $owner,
                ],
                'category' => [
                    'name' => $category,
                ],
                'publishDate' => now(),
            ];

            CrawlFinished::dispatch($post, 'github', 'markdown');
        }

        Auth::logout();

        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));
    }
}
