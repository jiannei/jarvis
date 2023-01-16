<?php

namespace App\Enums;

use Jiannei\Enum\Laravel\Enum;

class CrawlEnum extends Enum
{
    public const GITHUB_TRENDING = 'https://github.com/trending';

    public const LARAVEL_NEWS = 'https://laravel-news.com';
}
