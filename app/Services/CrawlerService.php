<?php

namespace App\Services;

use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class CrawlerService extends Service
{
    public function fetch(string $url,array $rules)
    {
       return Crawler::fetch($url)->rules($rules);
    }
}
