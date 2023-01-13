<?php

namespace App\Services;

use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class CrawlerService extends Service
{
    public function handleGithubTrending(?string $language = null,?array $query = [])
    {
        $url = $language ? "https://github.com/trending/{$language}" : "https://github.com/trending";
        $crawler = Crawler::fetch($url, $query);

        $rules = [
            'repo' => ['h1 a', 'href'],
            'desc' => ['p', 'text'],
            'language' => ["span[itemprop='programmingLanguage']", 'text'],
            'stars' => ['div.f6.color-fg-muted.mt-2 > a:nth-of-type(1)', 'text'],
            'forks' => ['div.f6.color-fg-muted.mt-2 > a:nth-of-type(2)', 'text'],
            'added_stars' => ['div.f6.color-fg-muted.mt-2 > span.d-inline-block.float-sm-right', 'text'],
        ];

        return $crawler->filter('article')->rules($rules);
    }
}
