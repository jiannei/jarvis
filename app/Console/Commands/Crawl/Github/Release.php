<?php

namespace App\Console\Commands\Crawl\Github;

use App\Events\CrawlFinished;
use App\Services\CrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class Release extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawl:github:release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新「Github Release」';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CrawlerService $service)
    {
        $this->info("[{$this->description}]:执行开始 ".now()->format('Y-m-d H:i:s'));

        Auth::loginUsingId(1);

        $repos = $service->handleGithubStarred(['sort' => 'updated', 'per_page' => 100]);

        foreach ($repos as $repo) {
            $this->comment('正在获取：'.$repo['full_name']);

            $releases = $service->handleGithubRelease($repo['owner'], $repo['name']);

            $desc = "## Release Notes\r\n";
            foreach ($releases as $release) {
                $desc .= ($release['name'].' '.$release['published_at']."\r\n"."### Changed\r\n".$release['body']."\r\n");
            }

            $post = [
                'title' => $repo['name'].' release notes',
                'link' => $repo['html_url'].'/releases',
                'description' => $desc,
                'author' => [
                    'name' => $repo['owner'],
                ],
                'category' => [
                    'name' => 'release',
                ],
                'publishDate' => $repo['created_at'],
            ];

            CrawlFinished::dispatch($post, 'github', 'markdown');
        }

        Auth::logout();
        $this->info("[{$this->description}]:执行结束 ".now()->format('Y-m-d H:i:s'));

        return self::SUCCESS;
    }
}
