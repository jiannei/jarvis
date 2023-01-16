<?php

namespace App\Enums;

class ScheduleEnum extends \Jiannei\Schedule\Laravel\Repositories\Enums\ScheduleEnum
{
    const GITHUB_TRENDING = 'crawl:github:trending';
    const LARAVEL_NEWS_BLOG = 'crawl:laravel-news:blog';
}
