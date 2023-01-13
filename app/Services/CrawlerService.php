<?php

namespace App\Services;

use App\Models\Mongodb\GithubTrendingDaily;
use App\Models\Mongodb\GithubTrendingLanguage;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class CrawlerService extends Service
{
    private $trendingUrl = "https://github.com/trending";

    public function handleGithubTrending(?string $language = null,?array $query = [])
    {
        $url = $language ? "{$this->trendingUrl}/{$language}" : $this->trendingUrl;
        $crawler = Crawler::fetch($url, $query);

        $rules = [
            'repo' => ['h1 a', 'href'],
            'desc' => ['p', 'text'],
            'language' => ["span[itemprop='programmingLanguage']", 'text'],
            'stars' => ['div.f6.color-fg-muted.mt-2 > a:nth-of-type(1)', 'text'],
            'forks' => ['div.f6.color-fg-muted.mt-2 > a:nth-of-type(2)', 'text'],
            'added_stars' => ['div.f6.color-fg-muted.mt-2 > span.d-inline-block.float-sm-right', 'text'],
        ];

        $trendings =  $crawler->filter('article')->rules($rules);

        foreach ($trendings as $trending) {
            GithubTrendingDaily::updateOrCreate([
                'day' => now()->format('Y-m-d'),
                'repo' => $trending['repo']
            ],$trending);
        }

        return $trendings;
    }

    public function handleGithubTrendingLanguages()
    {
        $crawler = Crawler::fetch($this->trendingUrl);

        $rules = [
            "code" => ["", "href"],
            "name" => ["span", "text"]
        ];

        $trendings = $crawler->filter("#languages-menuitems a[role='menuitemradio']")->rules($rules);
        foreach ($trendings as $trending) {
            GithubTrendingLanguage::updateOrCreate(['code' => $trending['code']],$trending);
        }

        return $trendings;
    }


    public function handleGithubTrendingSpokenLanguages()
    {
        $crawler = Crawler::fetch($this->trendingUrl);

        $rules = [
            "code" => ["", "href"],
            "name" => ["span", "text"]
        ];

        $trendings = $crawler->filter("div[data-filterable-for='text-filter-field-spoken-language'] a[role='menuitemradio']")->rules($rules);

        foreach ($trendings as $trending) {
            GithubTrendingLanguage::updateOrCreate(['code' => $trending['code']],$trending);
        }

        return $trendings;
    }
}
