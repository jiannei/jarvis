<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Jiannei\Schedule\Laravel\Repositories\Models\Schedule;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $schedules = [
            [
                'description' => '更新 Github daily 每日趋势',
                'command' => 'crawl:github:trending',
                'parameters' => 'all',
                'expression' => '*/30 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'github_trending.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 Github daily 每日趋势[中文区]',
                'command' => 'crawl:github:trending',
                'parameters' => 'all --spoken_language_code=zh',
                'expression' => '*/30 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'github_trending.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 Github daily 每周趋势',
                'command' => 'crawl:github:trending',
                'parameters' => 'all --since=weekly',
                'expression' => '10 3 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'github_trending.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 Github daily 每周趋势[中文区]',
                'command' => 'crawl:github:trending',
                'parameters' => 'all --since=weekly --spoken_language_code=zh',
                'expression' => '10 3 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'github_trending.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 Github daily 每月趋势',
                'command' => 'crawl:github:trending',
                'parameters' => 'all --since=monthly',
                'expression' => '0 0 * * 5',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'github_trending.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 Github daily 每月趋势[中文区]',
                'command' => 'crawl:github:trending',
                'parameters' => 'all --since=monthly --spoken_language_code=zh',
                'expression' => '0 0 * * 5',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'github_trending.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 laravel-news blog',
                'command' => 'crawl:laravel-news:blog',
                'parameters' => 'all',
                'expression' => '0 3 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'laravel-news.log',
                'output_append' => true,
            ],
            [
                'description' => '更新阮一峰科技爱好者周刊',
                'command' => 'crawl:ruanyf:weekly',
                'parameters' => '',
                'expression' => '0 10 * * 5',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'ruanyf-weekly.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 v2ex',
                'command' => 'crawl:v2ex',
                'parameters' => '',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'v2ex.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 v2ex[热门]',
                'command' => 'crawl:v2ex --tab=hot',
                'parameters' => '',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'v2ex.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 LearnKu[laravel]',
                'command' => 'crawl:learnku laravel',
                'parameters' => '',
                'expression' => '*/15 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'learnku.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 LearnKu[php]',
                'command' => 'crawl:learnku php',
                'parameters' => '',
                'expression' => '*/15 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'learnku.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 LearnKu[go]',
                'command' => 'crawl:learnku go',
                'parameters' => '',
                'expression' => '*/15 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'learnku.log',
                'output_append' => true,
            ],
            [
                'description' => '更新张鑫旭博客',
                'command' => 'crawl:zxx',
                'parameters' => '',
                'expression' => '0 0 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'zxx.log',
                'output_append' => true,
            ],
            [
                'description' => '更新技术胖博客',
                'command' => 'crawl:jsp',
                'parameters' => '',
                'expression' => '0 0 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'jspang.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 Github Releases',
                'command' => 'crawl:github:release',
                'parameters' => '',
                'expression' => '*/15 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'github-release.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 CXY521 导航[链接]',
                'command' => 'crawl:cxy521',
                'parameters' => '',
                'expression' => '0 0 * * 0',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'cxy521.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 CXY521 导航[手册]',
                'command' => 'crawl:cxy521 --page=manual',
                'parameters' => '',
                'expression' => '0 0 * * 0',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'cxy521.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 CXY521 导航[书籍]',
                'command' => 'crawl:cxy521 --page=book',
                'parameters' => '',
                'expression' => '0 0 * * 0',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'cxy521.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 laravel-tips',
                'command' => 'crawl:github:laravel-tips',
                'parameters' => '',
                'expression' => '0 0 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'laravel-tips.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 Airing weekly',
                'command' => 'crawl:airing-weekly',
                'parameters' => '',
                'expression' => '0 0 * * 0',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'airing-weekly.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 DechoHack 产品周刊',
                'command' => 'crawl:decohack',
                'parameters' => '',
                'expression' => '0 0 * * 0',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'decohack.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 Packagist',
                'command' => 'crawl:packagist',
                'parameters' => '',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'packagist.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 少数派',
                'command' => 'crawl:sspai',
                'parameters' => '',
                'expression' => '0,30 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'sspai.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 开源中国',
                'command' => 'crawl:oschina',
                'parameters' => '',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'oschina.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 知乎每日精选',
                'command' => 'crawl:zhihu',
                'parameters' => '',
                'expression' => '0 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'zhihu.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 月光博客',
                'command' => 'crawl:williamlong',
                'parameters' => '',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'williamlong.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 小众软件',
                'command' => 'crawl:appinn',
                'parameters' => '',
                'expression' => '0 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'appinn.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 异次元软件世界',
                'command' => 'crawl:iplaysoft',
                'parameters' => '',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'iplaysoft.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「git-book」',
                'command' => 'app:crawl:git-book',
                'parameters' => '',
                'expression' => '0 0 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'git-book.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「study-golang」',
                'command' => 'app:crawl:study-golang',
                'parameters' => '',
                'expression' => '0 0 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'study-golang.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「hello-github」',
                'command' => 'app:crawl:hello-github',
                'parameters' => '',
                'expression' => '0 0 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'hello-github.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「ModelScope」魔搭社区',
                'command' => 'app:crawl:model-scope',
                'parameters' => '',
                'expression' => '0 0 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'model-scope.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「Segmentfault」',
                'command' => 'app:crawl:segmentfault',
                'parameters' => '',
                'expression' => '0 1 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'segmentfault.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「TesterHome」最新发布',
                'command' => 'app:crawl:tester-home',
                'parameters' => '',
                'expression' => '0 1 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'tester-home.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「技术头条」最新分享',
                'command' => 'app:crawl:blog-read',
                'parameters' => '',
                'expression' => '0 1 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'blog-read.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「掘金」资讯',
                'command' => 'app:crawl:juejin',
                'parameters' => '',
                'expression' => '0 2 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'juejin.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「前端早早聊」文章',
                'command' => 'app:crawl:zaozao',
                'parameters' => '',
                'expression' => '0 3 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'zaozao.log',
                'output_append' => true,
            ],
            [
                'description' => '更新「博客园」资讯',
                'command' => 'app:crawl:cnblogs',
                'parameters' => '',
                'expression' => '0 4 * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'cnblogs.log',
                'output_append' => true,
            ],
            [
                'description' => '更新 Github daily 每日趋势',
                'command' => 'crawl:github:trending',
                'parameters' => 'all',
                'expression' => '*/30 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'github_trending.log',
                'output_append' => true,
            ],
        ];

        Schedule::truncate();
        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
