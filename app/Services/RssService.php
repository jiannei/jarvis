<?php

namespace App\Services;

use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class RssService extends Service
{
    public function handleRuanyfWeekly()
    {
        $crawler = Crawler::fetch('https://feeds.feedburner.com/ruanyifeng');

        $channel = [
            'title' => $crawler->filter('channel title')->text(),
            'link' => $crawler->filter('channel link')->text(),
            'description' => $crawler->filter('channel description')->text(),
            'lastBuildDate' => $crawler->filter('channel lastBuildDate')->text(),
        ];

        $items = $crawler->filter('channel item')->rules([
            'category' => ['category','text'],
            'title' => ['title','text'],
            'description' => ['description','text'],
            'link' => ['link','text'],
            'guid' => ['guid','text'],
            'pubDate' => ['pubDate','text'],
        ]);

        return compact('channel','items');
    }
}
