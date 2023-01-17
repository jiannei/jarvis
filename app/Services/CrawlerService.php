<?php

namespace App\Services;

use App\Enums\CrawlEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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
            'publishDate' => ['p', 'text'],
        ];

        return $crawler->filter('main > div:last-child > ul > li:nth-of-type(n+2)')->rules($rules);
    }

    public function handleLaravelNewsBlog($link): array
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
            'intro' => $authorDom->filter('p:last-child')->count() ? $authorDom->filter('p:last-child')->text() : null,
        ];

        return [
            'title' => $title,
            'category' => $category,
            'author' => $author,
            'description' => $article,
            'publishDate' => $publishedAt,
            'link' => CrawlEnum::LARAVEL_NEWS."/{$link}",
        ];
    }

    public function handleRuanyfWeeklyLatest(?int $period = null)
    {
        $issues = $this->handleRuanyfWeekly();

        $issues = array_reverse($issues);

        $period = $period ?: count($issues);

        try {
            $path = $issues[$period - 1]['path'];
            $title = $issues[$period - 1]['title'];
        } catch (\Exception $exception) {
            abort(404);
        }

        // latest
        $content = Crawler::client()
            ->accept('application/vnd.github.raw+json')
            ->withToken(Auth::user()->github_token)
            ->get('https://api.github.com/repos/ruanyf/weekly/contents/'.$path);

        return [
            'title' => $title,
            'category' => [
                'name' => 'weekly',
                'link' => 'https://www.ruanyifeng.com/blog',
            ],
            'author' => [
                'name' => 'ruanyf',
            ],
            'description' => $content->body(),
            'publishDate' => Carbon::now()->format('Y-m-d'),
            'link' => 'https://github.com/ruanyf/weekly/blob/master/'.$path,
        ];
    }

    public function handleRuanyfWeekly()
    {
        $crawler = Crawler::fetch('https://api.github.com/repos/ruanyf/weekly/readme',null,[
            'headers' => [
                'Accept' => 'application/vnd.github.html+json',
                'Authorization' => 'Bearer '.Auth::user()->github_token,
            ],
        ]);

        return $crawler->filter('article ul li')->rules([
            'path' => ['a', 'href'],
            'title' => ['a', 'text'],
        ]);
    }
}
