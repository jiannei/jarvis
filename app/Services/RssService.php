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

        $items = $crawler->group('channel item')->parse([
            'category' => ['category', 'text'],
            'title' => ['title', 'text'],
            'description' => ['description', 'text'],
            'link' => ['link', 'text'],
            'guid' => ['guid', 'text'],
            'pubDate' => ['pubDate', 'text'],
        ]);

        return compact('channel', 'items');
    }

    public function handleZhangXinxuBlog()
    {
        $crawler = Crawler::fetch('https://www.zhangxinxu.com/wordpress/feed/');

        // TODO
        $channel = [
            'title' => $crawler->filter('channel title')->text(),
            'link' => $crawler->filter('channel link')->text(),
            'description' => $crawler->filter("content\:encoded")->text(),
            'lastBuildDate' => $crawler->filter('channel lastBuildDate')->text(),
        ];

        $items = $crawler->group('channel item')->parse([
            'category' => ['category', 'text'],
            'title' => ['title', 'text'],
            'description' => ['content\:encoded', 'text'],
            'link' => ['link', 'text'],
            'guid' => ['guid', 'text'],
            'pubDate' => ['pubDate', 'text'],
        ]);

        return compact('channel', 'items');
    }

    public function handleAiringWeekly()
    {
        $crawler = Crawler::fetch('https://weekly.ursb.me/index.xml');

        $channel = [
            'title' => $crawler->filter('channel title')->text(),
            'link' => $crawler->filter('channel link')->text(),
            'description' => $crawler->filter('channel description')->text(),
            'lastBuildDate' => $crawler->filter('channel lastBuildDate')->text(),
        ];

        $items = $crawler->group('channel item')->parse([
            'category' => ['category', 'text'],
            'title' => ['title', 'text'],
            'description' => ['description', 'text'],
            'link' => ['link', 'text'],
            'guid' => ['guid', 'text'],
            'pubDate' => ['pubDate', 'text'],
        ]);

        return compact('channel', 'items');
    }

    public function handleViggoDecoHack()
    {
        // todo
        $crawler = Crawler::fetch('https://www.decohack.com/feed', null, ['verify' => false]);

        $channel = [
            'title' => $crawler->filter('channel title')->text(),
            'link' => $crawler->filter('channel link')->text(),
            'description' => $crawler->filter('channel description')->text(),
            'lastBuildDate' => $crawler->filter('channel lastBuildDate')->text(),
        ];

        $items = $crawler->group('channel item')->parse([
            'category' => ['category', 'text'],
            'title' => ['title', 'text'],
            'description' => ['content\:encoded', 'text'],
            'link' => ['link', 'text'],
            'guid' => ['guid', 'text'],
            'pubDate' => ['pubDate', 'text'],
        ]);

        return compact('channel', 'items');
    }

    public function handlePackagist()
    {
        return Crawler::rss('https://packagist.org/feeds/packages.rss');
    }
}
