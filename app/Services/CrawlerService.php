<?php

namespace App\Services;

use App\Enums\CrawlEnum;
use App\Events\CrawlFinished;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class CrawlerService extends Service
{
    public function handleGithubTrending(?string $language = null, ?array $query = []): array
    {
        $url = $language ? CrawlEnum::GITHUB_TRENDING."/{$language}" : CrawlEnum::GITHUB_TRENDING;
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

    public function handleGithubTrendingLanguages(): array
    {
        $crawler = Crawler::fetch(CrawlEnum::GITHUB_TRENDING);

        $rules = [
            'code' => ['', 'href'],
            'name' => ['span', 'text'],
        ];

        return $crawler->filter("#languages-menuitems a[role='menuitemradio']")->rules($rules);
    }

    public function handleGithubTrendingSpokenLanguages(): array
    {
        $crawler = Crawler::fetch(CrawlEnum::GITHUB_TRENDING);

        $rules = [
            'code' => ['', 'href'],
            'name' => ['span', 'text'],
        ];

        return $crawler->filter("div[data-filterable-for='text-filter-field-spoken-language'] a[role='menuitemradio']")->rules($rules);
    }

    public function handleLaravelNewsBlogs(): array
    {
        $crawler = Crawler::fetch(CrawlEnum::LARAVEL_NEWS.'/blog');

        $rules = [
            'link' => ['a', 'href'],
            'title' => ['h4 > span', 'text'],
            'summary' => ['h4 + p', 'text'],
            'published_at' => ['p', 'text'],
        ];

        return $crawler->filter('main > div:last-child > ul > li:nth-of-type(n+2)')->rules($rules);
    }

    public function handleLaravelNewsBlog($link)
    {
        $crawler = Crawler::fetch(CrawlEnum::LARAVEL_NEWS."/{$link}");

        $title = $crawler->filter('h1')->text();

        $category = [
            'link' => CrawlEnum::LARAVEL_NEWS.$crawler->filter('h1 + div a')->attr('href'),
            'name' => $crawler->filter('h1 + div a')->text(),
        ];

        $publishedAt = $crawler->filter('h1 + div p')->text();

        $article = $crawler->filter('article div:nth-of-type(2) div:nth-of-type(1)')->html();

        $authorDom = $crawler->filter('article div:nth-of-type(2) div:nth-of-type(2) > div:last-child ');

        $author = [
            'name' => $authorDom->filter("a[rel='author']")->text(),
            'homepage' => CrawlEnum::LARAVEL_NEWS.$authorDom->filter("a[rel='author']")->attr('href'),
            // 'intro' => $authorDom->filter('p:last-child')->text(),// todo
        ];

        CrawlFinished::dispatch($link, $article, 'laravel-news');

        return [
            'title' => $title,
            'category' => $category,
            'author' => $author,
            'content' => $article,
            'published_at' => $publishedAt,
            'link' => CrawlEnum::LARAVEL_NEWS."/{$link}",
        ];
    }
}
