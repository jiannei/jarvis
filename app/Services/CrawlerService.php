<?php

namespace App\Services;

use App\Enums\CrawlEnum;
use App\Events\CrawlFinished;
use App\Models\Github\TrendingDaily;
use App\Models\Github\TrendingLanguage;
use App\Models\Github\TrendingSpokenLanguage;
use Carbon\CarbonInterface;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Jiannei\LaravelCrawler\Contracts\ConsumeService;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class CrawlerService extends Service implements ConsumeService
{
    public function handleLaravelNewsBlog($link): array
    {
        // 单个解析，不带 group
        return Crawler::pattern([
            'url' => CrawlEnum::LARAVEL_NEWS."/{$link}",
            'rules' => [
                'title' => ['h1', 'text'],
                'category.link' => [
                    'h1 + div a', 'href', null, function ($node, Stringable $val) {
                        return $val->start(CrawlEnum::LARAVEL_NEWS);
                    },
                ],
                'category.name' => ['h1 + div a', 'text'],
                'author.name' => ["article div:nth-of-type(2) div:nth-of-type(2) > div:last-child a[rel='author']", 'text'],
                'author.homepage' => [
                    "article div:nth-of-type(2) div:nth-of-type(2) > div:last-child a[rel='author']", 'href', null, function ($node, Stringable $val) {
                        return $val->start(CrawlEnum::LARAVEL_NEWS);
                    },
                ],
                'author.intro' => ['article div:nth-of-type(2) div:nth-of-type(2) > div:last-child p:last-child', 'text'],
                'description' => ['article div:nth-of-type(2) div:nth-of-type(1)', 'html'],
                'publishDate' => [
                    'h1 + div p', 'text', null, function ($node, $val) {
                        return Carbon::createFromTimestamp(strtotime($val))->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);
                    },
                ],
                'link' => CrawlEnum::LARAVEL_NEWS."/{$link}",
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

    public function handleIndependentBlogs()
    {
        $channel = [
            'link' => 'https://github.com/timqian/chinese-independent-blogs',
            'author' => 'https://github.com/timqian',
        ];

        $items = Crawler::json('github:independent-blogs', [], [
            'headers' => [
                'Accept' => 'application/vnd.github.html+json',
                'Authorization' => 'Bearer '.Auth::user()->github_token,
            ],
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

    public function handleLearnKu(string $community)
    {
        $crawler = Crawler::fetch('https://learnku.com/'.$community);

        return $crawler->group('.topic-list .simple-topic')->parse([
            'avatar' => ['.user-avatar img', 'src'],
            'category' => ['.category-name', 'text'],
            'title' => ['.topic-title', 'text'],
            'link' => ['.user-avatar a', 'href'],
            'replies' => ['.count_of_replies', 'text'],
            'updated_at' => ['.timeago', 'title'],
        ])->filter(function ($topic) {
            return ! in_array($topic['category'], ['置顶', '广告']);
        })->values();
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

    public function handleJspang()
    {
        // SSL certificate problem: unable to get local issuer certificate
        $crawler = Crawler::fetch('https://jspang.com', null, ['verify' => false]);

        // TODO filter selector => rules 对应的 json 文件配置，返回 collection
        $blogs = $crawler->group('.blog-list .blog-item')->parse([
            'title' => ['.item-title', 'text'],
            'link' => ['.item-title a', 'href'],
            'category' => ['.item-tag span', 'text', 1],
            'publishDate' => ['.item-tag span', 'text', 0],
            'views' => ['.item-tag span', 'text', 2],
            'summary' => ['.item-desc', 'text'],
        ])->all();

        $videos = $crawler->group('.left-video-box li')->parse([
            'link' => ['a', 'href'],
            'cover' => ['.video-image img', 'src'],
            'title' => ['.video-title', 'text'],
        ])->all();

        return compact('blogs', 'videos');
    }

    public function handleJspangPost($link)
    {
        $crawler = Crawler::fetch('https://jspang.com'.$link, null, ['verify' => false]);

        $post = $crawler->group('.main-box')->parse([
            'title' => ['.article-title h1', 'text'],
            'category' => ['.remarks span b', 'text', 0],
            'publishDate' => ['.remarks span b', 'text', 1],
            'videos' => ['.remarks span b', 'text', 2],
            'views' => ['.remarks span b', 'text', 3],
            'time' => ['.remarks span b', 'text', 4],
            'summary' => ['.introduce-html', 'text'],
            'description' => ['.article-details', 'html'],
        ])->first();

        return [
            'title' => $post['title'],
            'link' => 'https://jspang.com'.$link,
            'description' => $post['summary'].$post['description'],
            'author' => [
                'name' => 'JSPang',
            ],
            'category' => [
                'name' => $post['category'],
            ],
            'publishDate' => $post['publishDate'],
        ];
    }

    public function handleGithubStarred($query)
    {
        $repos = Http::withToken(Auth::user()->github_token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->throw()
            ->get('https://api.github.com/user/starred', $query)
            ->json();

        return Arr::map($repos, function ($repo) {
            return [
                'owner' => $repo['owner']['login'],
                'name' => $repo['name'],
                'full_name' => $repo['full_name'],
                'html_url' => $repo['html_url'],
                'description' => $repo['description'],
                'homepage' => $repo['homepage'],
                'language' => $repo['language'],
                'stargazers_count' => $repo['stargazers_count'],
                'watchers_count' => $repo['watchers_count'],
                'forks_count' => $repo['forks_count'],
                'created_at' => Carbon::createFromTimestamp(strtotime($repo['created_at']))->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::createFromTimestamp(strtotime($repo['updated_at']))->format('Y-m-d H:i:s'),
                'pushed_at' => Carbon::createFromTimestamp(strtotime($repo['pushed_at']))->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function handleGithubRelease(string $owner, string $repo)
    {
        $releases = Http::withToken(Auth::user()->github_token)
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->throw()
            ->get("https://api.github.com/repos/$owner/$repo/releases")
            ->json();

        return Arr::map($releases, function ($release) {
            return [
                'name' => $release['name'],
                'body' => $release['body'],
                'tag' => $release['tag_name'],
                'created_at' => Carbon::createFromTimestamp(strtotime($release['created_at']))->format('Y-m-d H:i:s'),
                'published_at' => Carbon::createFromTimestamp(strtotime($release['published_at']))->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function handleCxy521(string $page = 'index')
    {
        $crawler = Crawler::fetch("https://www.cxy521.com/{$page}.html");

        // TODO 列表套列表
        $categories = $crawler->group('.main-content .indexbox:nth-of-type(n+2)')->parse([
            'title' => ['.indexbox_title strong', 'text'],
        ])->all();

        $data = [];
        foreach ($categories as $index => $category) {
            $pos = 2 * ($index + 1) + 1;
            $links = $crawler->group(".main-content .indexbox:nth-of-type({$pos}) li")->parse([
                'icon' => ['img', 'src'],
                'link' => ['a', 'href'],
                'description' => ['p', 'text'],
            ])->all();

            foreach ($links as $key => $item) {
                if (! $item['link']) {
                    unset($links[$key]);

                    continue;
                }

                $links[$key]['icon'] = 'https://www.cxy521.com'.trim($item['icon'], '.');
            }

            $data[$index] = [
                'category' => $category['title'],
                'links' => array_values($links),
            ];
        }

        return $data;
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

    public function handleViggoDecoHack()
    {
        $crawler = Crawler::fetch('https://www.v2ex.com/member/ViggoZ/topics');

        // TODO 自动分页
        return $crawler->group('.item')->parse([
            'title' => ['.topic-link', 'text'],
            'link' => ['.topic-link', 'href'],
            'node_name' => ['.node', 'text'],
            'node_link' => ['.node', 'href'],
            'member_name' => ['strong', 'text'],
            'member_link' => ['strong a', 'href'],
        ]);
    }

    public function handleLaracasts(string $menu)
    {
        $crawler = Crawler::chrome("https://laracasts.com/{$menu}", WebDriverExpectedCondition::titleIs('Laracasts Series'));

        // 单个元素提取公共 selector，使用 filter
        // TODO 单个元素中嵌套列表元素
        $section1 = $crawler->filter('section:nth-of-type(1)')->parse([
            'title' => ['header h3', 'text'],
            'desc' => ['header p', 'text'],
            'post.desc' => ['.featured-collection .content', 'text'],
        ]);

        // $section1Posts = $crawler->filter();

        dd($section1);

        $post = $crawler->group('.featured-collection')->parse([
            'title' => ['h3 a', 'text'],
            'link' => ['h3 a', 'href'],
            'desc' => ['.content', 'text'],
        ])->filter(function ($item) {
            return $item['title'] && $item['link'];
        })->values();

        dd($header, $post);
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

    public function githubTrending(array $content)
    {
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
    }

    public function githubTrendingLanguage(array $language)
    {
        TrendingLanguage::updateOrCreate(['code' => $language['code']], $language);
    }

    public function githubTrendingSpokenLanguage(array $spokenLanguage)
    {
        TrendingSpokenLanguage::updateOrCreate(['code' => $spokenLanguage['code']], $spokenLanguage);
    }

    public function laravelNewsBlog(array $content)
    {
        return true;
    }

    public function v2ex(array $content)
    {
        $topicId = explode('#', explode('/', $content['link'])[2])[0];
        $topic = $this->handleV2exTopic($topicId);

        CrawlFinished::dispatch($topic, 'v2ex', 'markdown');
    }

    public function gitee(array $content)
    {
        return [];
    }

    public function airingWeekly(array $content)
    {
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
    }

    public function appinn(array $post)
    {
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
    }

    public function blogRead(array $post)
    {
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
    }

    private function cnblogs(array $post)
    {
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
    }

    public function cnblogsAggsiteTopdiggs(array $content)
    {
        $this->cnblogs($content);
    }

    public function cnblogsAggsiteTopviews(array $content)
    {
        $this->cnblogs($content);
    }

    public function cnblogsAggsiteHeadline(array $content)
    {
        $this->cnblogs($content);
    }

    public function cnblogsCateGo(array $content)
    {
        $this->cnblogs($content);
    }

    public function cnblogsCatePhp(array $content)
    {
        $this->cnblogs($content);
    }

    public function cnblogsCateVue(array $content)
    {
        $this->cnblogs($content);
    }

    public function cnblogsCateJavascript(array $content)
    {
        $this->cnblogs($content);
    }

    public function cnblogsCateReact(array $content)
    {
        $this->cnblogs($content);
    }

    public function cnblogsPick(array $content)
    {
        $this->cnblogs($content);
    }

    public function gitBook(array $post)
    {
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
    }

    public function helloGithub(array $post)
    {
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
    }

    public function huxiu(array $post)
    {
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
    }

    public function ifanr(array $post)
    {
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
    }

    public function infoQ(array $post)
    {
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
    }

    public function iplaysoft(array $post)
    {
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
    }

    private function juejin(array $post)
    {
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
    }

    public function juejinCategoryFrontend(array $content)
    {
        $this->juejin($content);
    }

    public function juejinCategoryBackend(array $content)
    {
        $this->juejin($content);
    }

    public function juejinCategoryAi(array $content)
    {
        $this->juejin($content);
    }

    public function juejinCategoryFreebie(array $content)
    {
        $this->juejin($content);
    }

    public function juejinCategoryCareer(array $content)
    {
        $this->juejin($content);
    }

    public function juejinCategoryArticle(array $content)
    {
        $this->juejin($content);
    }

    public function modelscopeDatasets(array $post)
    {
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
    }

    public function oschina(array $post)
    {
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
    }

    public function packages(array $post)
    {
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
    }

    private function segmentfault($post)
    {
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
    }

    public function segmentfaultChannelFrontend(array $post)
    {
        $this->segmentfault($post);
    }

    public function segmentfaultChannelBackend(array $post)
    {
        $this->segmentfault($post);
    }

    public function segmentfaultChannelMiniprogram(array $post)
    {
        $this->segmentfault($post);
    }

    public function segmentfaultChannelToolkit(array $post)
    {
        $this->segmentfault($post);
    }

    public function sspai(array $post)
    {
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
    }

    public function studygolangGoDaily(array $post)
    {
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
    }

    public function testerhomeNewest(array $post)
    {
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
    }

    public function williamlong(array $post)
    {
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
    }

    public function zaozaoArticleQuality(array $post)
    {
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
    }

    public function zxxBlog(array $blog)
    {
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
    }

    public function zhihu(array $post)
    {
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
    }
}
