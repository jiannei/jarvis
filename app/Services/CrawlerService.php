<?php

namespace App\Services;

use App\Enums\CrawlEnum;
use App\Events\CrawlFinished;
use App\Models\Github\TrendingDaily;
use App\Models\Github\TrendingLanguage;
use App\Models\Github\TrendingSpokenLanguage;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Jiannei\LaravelCrawler\Contracts\ConsumeService;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;
use Jiannei\LaravelCrawler\Support\Traits\Consumable;

class CrawlerService extends Service implements ConsumeService
{
    use Consumable;

    public function handleLaravelNewsBlog($link): array
    {
        // 单个解析，不带 group
        return Crawler::pattern([
            'url' => "https://laravel-news.com/{$link}",
            'rules' => [
                'title' => ['h1', 'text'],
                'category.link' => [
                    'h1 + div a', 'href', null, function ($node, Stringable $val) {
                        return $val->start('https://laravel-news.com/');
                    },
                ],
                'category.name' => ['h1 + div a', 'text'],
                'author.name' => ["article div:nth-of-type(2) div:nth-of-type(2) > div:last-child a[rel='author']", 'text'],
                'author.homepage' => [
                    "article div:nth-of-type(2) div:nth-of-type(2) > div:last-child a[rel='author']", 'href', null, function ($node, Stringable $val) {
                        return $val->start('https://laravel-news.com/');
                    },
                ],
                'author.intro' => ['article div:nth-of-type(2) div:nth-of-type(2) > div:last-child p:last-child', 'text'],
                'description' => ['article div:nth-of-type(2) div:nth-of-type(1)', 'html'],
                'publishDate' => [
                    'h1 + div p', 'text', null, function ($node, $val) {
                        return Carbon::createFromTimestamp(strtotime($val))->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);
                    },
                ],
                'link' => "https://laravel-news.com//{$link}",
            ],
        ]);
    }

    public function handleRuanyfWeeklyLatest(?int $period = null)
    {
        $issues = $this->handleRuanyfWeekly();

        $issues['items'] = array_reverse($issues['items']);

        $period = $period ?: count($issues['items']);

        try {
            $path = $issues['items'][$period - 1]['path'];
            $title = $issues['items'][$period - 1]['title'];
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
        $crawler = Crawler::fetch('https://api.github.com/repos/ruanyf/weekly/readme', null, [
            'headers' => [
                'Accept' => 'application/vnd.github.html+json',
                'Authorization' => 'Bearer '.Auth::user()->github_token,
            ],
        ]);

        $channel = [
            'link' => 'https://github.com/ruanyf/weekly',
            'author' => 'https://github.com/ruanyf',
        ];

        $items = $crawler->group('article ul li')->parse([
            'path' => ['a', 'href'],
            'title' => ['a', 'text'],
        ])->all();

        return compact('channel', 'items');
    }

    public function handleV2exTopic($topicId)
    {
        $url = 'https://www.v2ex.com/api/v2/topics/'.$topicId;

        $topic = Http::withToken(config('services.v2ex.token'))->get($url)->throw()->json();

        return [
            'title' => $topic['result']['title'],
            'link' => $topic['result']['url'],
            'description' => $topic['result']['content'],
            'author' => [
                'name' => $topic['result']['member']['username'],
            ],
            'category' => [
                'name' => $topic['result']['node']['name'],
            ],
            'publishDate' => Carbon::createFromTimestamp($topic['result']['created'])->format('Y-m-d H:i:s'),
        ];
    }

    public function handleLearnKuTopic($topicUrl)
    {
        $crawler = Crawler::fetch($topicUrl);

        $content = $crawler->filter('.article-content .content-body')->remove([
            ['.toc-wraper', 'outerHtml'],
            ['div', 'outerHtml', 'last'],
        ]);

        $publishDate = $crawler->filter('.book-article-meta a span')->attr('title');
        $category = $crawler->filter('.book-article-meta a')->first()->text();
        $title = $crawler->filter('h1')->text();

        // TODO Fixed me
        if (Str::contains($category, '博客')) {
            $author = $crawler->filter('.blog-article .content .header')->text('');
        } else {
            $author = $crawler->filter('.authors-box .content .header')->text('');
        }

        return [
            'title' => $title,
            'link' => $topicUrl,
            'description' => trim($content),
            'author' => [
                'name' => $author,
            ],
            'category' => [
                'name' => $category,
            ],
            'publishDate' => $publishDate,
        ];
    }

    public function handleLaravelTips($owner = 'LaravelDaily', $repo = 'laravel-tips', $branch = 'master')
    {
        $tips = Http::throw()
            ->get("https://api.github.com/repos/{$owner}/{$repo}/git/trees/{$branch}?recursive=1")
            ->collect('tree');

        return $tips->filter(function ($tip) {
            return ! Str::contains($tip['path'], 'README');
        })->map(function ($tip) use ($owner, $repo) {
            return [
                'path' => $tip['path'],
                'content' => Http::withToken(Auth::user()->github_token)
                    ->withHeaders(['Accept' => 'application/vnd.github.raw'])
                    ->throw()
                    ->get("https://api.github.com/repos/{$owner}/{$repo}/contents/{$tip['path']}")->body(),
            ];
        });
    }

    public function handleCrawl(string $key, array $query = [])
    {
        return Crawler::json($key, $query)->all();
    }

    public function handleGitee(string $explore = '/explore/all')
    {
        return Crawler::before(function ($url, $query, $option) use ($explore) {
            return [str_replace('{explore}', trim($explore, '/'), $url), $query, $option];
        })->json('gitee');
    }

    public function githubTrending()
    {
        return function ($content) {
            $content['stars'] = Str::remove(',', $content['stars']);
            $content['forks'] = Str::remove(',', $content['forks']);
            $content['added_stars'] = Str::remove([
                ',',
                ' star today', ' stars today',
                ' star this week', 'stars this week',
                ' star this month', 'stars this month',
            ], $content['added_stars']);
            $content['day'] = now()->format('Y-z');

            TrendingDaily::query()->updateOrCreate([
                'day' => $content['day'],
                'repo' => $content['repo'],
            ], $content);

            return true;
        };
    }

    public function githubTrendingLanguage()
    {
        return function (array $language) {
            TrendingLanguage::updateOrCreate(['code' => $language['code']], $language);

            return true;
        };

    }

    public function githubTrendingSpokenLanguage()
    {
        return function ($spokenLanguage) {
            TrendingSpokenLanguage::updateOrCreate(['code' => $spokenLanguage['code']], $spokenLanguage);

            return true;
        };
    }

    public function laravelNewsBlog()
    {
        return function () {
            return true;
        };
    }

    public function v2ex()
    {
        return function ($content) {
            $topicId = explode('#', explode('/', $content['link'])[2])[0];
            $topic = $this->handleV2exTopic($topicId);

            CrawlFinished::dispatch($topic, 'v2ex', 'markdown');

            return true;
        };
    }

    public function gitee()
    {
        return function () {
            return true;
        };
    }

    public function airingWeekly()
    {
        return function ($content) {
            $topic = [
                'title' => $content['title'],
                'link' => $content['link'],
                'description' => $content['description'],
                'author' => [
                    'name' => 'airing',
                ],
                'category' => [
                    'name' => $content['category'] ?? 'weekly',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($content['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'airing', 'html');

            return true;
        };
    }

    public function appinn()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'appinn',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'daily',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'appinn', 'html');

            return true;
        };
    }

    public function blogRead()
    {
        return function ($post) {
            if (! $post['title']) {
                throw new \RuntimeException('blogRead missing title');
            }

            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'BlogRead',
                ],
                'category' => [
                    'name' => 'BlogRead',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'BlogRead');

            return true;
        };
    }

    public function cnblogs()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'cnblogs',
                ],
                'category' => [
                    'name' => 'cnblogs',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'cnblogs');

            return true;
        };
    }

    public function gitBook()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'gitbook',
                ],
                'category' => [
                    'name' => 'gitbook',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'gitbook');

            return true;
        };
    }

    public function helloGithub()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'hellogithub',
                ],
                'category' => [
                    'name' => 'hellogithub',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'hellogithub');

            return true;
        };
    }

    public function huxiu()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'huxiu',
                ],
                'category' => [
                    'name' => 'huxiu',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'huxiu');

            return true;
        };
    }

    public function ifanr()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'ifanr',
                ],
                'category' => [
                    'name' => 'ifanr',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'ifanr');

            return true;
        };
    }

    public function infoQ()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'infoq',
                ],
                'category' => [
                    'name' => 'infoq',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'infoq');

            return true;
        };
    }

    public function iplaysoft()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'iplaysoft',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'iplaysoft',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'iplaysoft', 'markdown');

            return true;
        };
    }

    public function juejin()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'juejin',
                ],
                'category' => [
                    'name' => 'juejin',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'juejin');

            return true;
        };
    }

    public function modelscopeDatasets()
    {
        return function ($post) {
            if (! $post['title']) {
                throw new \RuntimeException('blogRead missing title');
            }

            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'ModelScope',
                ],
                'category' => [
                    'name' => 'ModelScope',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'ModelScope');

            return true;
        };
    }

    public function oschina()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'oschina',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'news',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'oschina', 'html');

            return true;
        };
    }

    public function packages()
    {
        return function ($post) {
            $topic = [
                'title' => 'packagist: '.$post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'packagist',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'daily',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'packagist', 'html');

            return true;
        };
    }

    public function segmentfault()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'segmentfault',
                ],
                'category' => [
                    'name' => 'segmentfault',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'segmentfault');

            return true;
        };
    }

    public function sspai()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'sspai',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'daily',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'sspai', 'html');

            return true;
        };
    }

    public function studygolangGoDaily()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'studygolang',
                ],
                'category' => [
                    'name' => 'studygolang',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'studygolang');

            return true;
        };
    }

    public function testerhomeNewest()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'TesterHome',
                ],
                'category' => [
                    'name' => 'TesterHome',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'TesterHome');

            return true;
        };
    }

    public function williamlong()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'williamlong',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'daily',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'williamlong', 'html');

            return true;
        };
    }

    public function zaozaoArticleQuality()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => $post['author'] ?? 'zaozao',
                ],
                'category' => [
                    'name' => 'zaozao',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'zaozao');

            return true;
        };
    }

    public function zxxBlog()
    {
        return function ($blog) {
            $topic = [
                'title' => $blog['title'],
                'link' => $blog['link'],
                'description' => $blog['description'],
                'author' => [
                    'name' => '张鑫旭',
                ],
                'category' => [
                    'name' => $blog['category'],
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($blog['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'zhangxinxu', 'html');

            return true;
        };
    }

    public function zhihu()
    {
        return function ($post) {
            $topic = [
                'title' => $post['title'],
                'link' => $post['link'],
                'description' => $post['description'],
                'author' => [
                    'name' => 'zhihu',
                ],
                'category' => [
                    'name' => $post['category'] ?? 'daily',
                ],
                'publishDate' => Carbon::createFromTimestamp(strtotime($post['pubDate']))->format('Y-m-d H:i:s'),
            ];

            CrawlFinished::dispatch($topic, 'zhihu', 'html');

            return true;
        };
    }

    public function learnKu()
    {
        return function ($content) {
            if (in_array($content['category'], ['置顶', '广告'])) {
                return false;
            }

            $topic = $this->handleLearnKuTopic($content['link']);

            CrawlFinished::dispatch($topic, 'learnku', 'html');

            return true;
        };
    }
}
