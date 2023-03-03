<?php

namespace App\Services;

use App\Enums\CrawlEnum;
use Carbon\CarbonInterface;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;

class CrawlerService extends Service
{
    public function handleGithubTrending(?string $language = null, ?array $query = []): array
    {
       return Crawler::pattern([
            'url' => $language ? CrawlEnum::GITHUB_TRENDING."/{$language}" : CrawlEnum::GITHUB_TRENDING,
            'query' => $query,
            'group' => 'article',
            'rules' => [
                'repo' => ['h1 a', 'href'],
                'desc' => ['p', 'text'],
                'language' => ["span[itemprop='programmingLanguage']", 'text'],
                'stars' => ['div.f6.color-fg-muted.mt-2 > a:nth-of-type(1)', 'text'],
                'forks' => ['div.f6.color-fg-muted.mt-2 > a:nth-of-type(2)', 'text'],
                'added_stars' => ['div.f6.color-fg-muted.mt-2 > span.d-inline-block.float-sm-right', 'text'],
            ]
        ])->all();
    }

    public function handleGithubTrendingLanguages(): array
    {
        return Crawler::pattern([
            'url' => CrawlEnum::GITHUB_TRENDING,
            'group' => "#languages-menuitems a[role='menuitemradio']",
            'rules' => [
                'code' => ['', 'href'],
                'name' => ['span', 'text'],
            ]
        ])->all();
    }

    public function handleGithubTrendingSpokenLanguages(): array
    {
        return Crawler::pattern([
            'url' => CrawlEnum::GITHUB_TRENDING,
            'group' => "div[data-filterable-for='text-filter-field-spoken-language'] a[role='menuitemradio']",
            'rules' => [
                'code' => ['', 'href'],
                'name' => ['span', 'text'],
            ]
        ])->all();
    }

    public function handleLaravelNewsBlogs(): array
    {
        return Crawler::pattern([
            'url' => CrawlEnum::LARAVEL_NEWS.'/blog',
            'group' => 'main > div:last-child > ul > li:nth-of-type(n+2)',
            'rules' => [
                'link' => ['a', 'href'],
                'title' => ['h4 > span', 'text'],
                'summary' => ['h4 + p', 'text'],
                'publishDate' => ['p', 'text'],
            ]
        ])->all();
    }

    public function handleLaravelNewsBlog($link): array
    {
        // 单个解析，不带 group
        return Crawler::pattern([
            'url' => CrawlEnum::LARAVEL_NEWS."/{$link}",
            'rules' => [
                'title' => ['h1','text'],
                'category.link' => ['h1 + div a','href',null, function ($node, Stringable $val) {
                    return $val->start(CrawlEnum::LARAVEL_NEWS);
                }],
                'category.name' => ['h1 + div a','text'],
                'author.name' => ["article div:nth-of-type(2) div:nth-of-type(2) > div:last-child a[rel='author']",'text'],
                'author.homepage' => ["article div:nth-of-type(2) div:nth-of-type(2) > div:last-child a[rel='author']",'href',null, function ($node,Stringable $val) {
                    return $val->start(CrawlEnum::LARAVEL_NEWS);
                }],
                'author.intro' => ['article div:nth-of-type(2) div:nth-of-type(2) > div:last-child p:last-child','text'],
                'description' => ['article div:nth-of-type(2) div:nth-of-type(1)','html'],
                'publishDate' => ['h1 + div p','text',null, function ($node,$val) {
                    return Carbon::createFromTimestamp(strtotime($val))->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT);
                }],
                'link' => CrawlEnum::LARAVEL_NEWS."/{$link}",
            ]
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

        $items =  Crawler::pattern([
            'url' => 'https://api.github.com/repos/timqian/chinese-independent-blogs/readme',
            'options' => [
                'headers' => [
                    'Accept' => 'application/vnd.github.html+json',
                    'Authorization' => 'Bearer '.Auth::user()->github_token,
                ],
            ],
            'group' => 'table tbody tr',
            'rules' => [
                'intro' => ['td:nth-child(2)', 'text'],
                'link' => ['td:nth-child(3)', 'text'],
                'tags' => ['td:nth-child(4)', 'text'],
            ],
        ]);

        return compact('channel', 'items');
    }

    public function handleV2ex($tab = null)
    {
        $url = 'https://www.v2ex.com/';

        return Crawler::pattern([
            'url' => $tab ? $url."?tab={$tab}" : $url,
            'group' => [
                '#Tabs a' => [
                    'label' => ['a', 'text'],
                    'value' => ['a', 'href'],
                ],
                '#SecondaryTabs a' => [
                    'label' => ['a', 'text'],
                    'value' => ['a', 'href'],
                ],
                'div .item table' => [
                    'member_avatar' => ['.avatar', 'src'],
                    'member_link' => ['strong a', 'href'],
                    'member_name' => ['strong a', 'text'],
                    'title' => ['.topic-link', 'text'],
                    'link' => ['.topic-link', 'href'],
                    'node_label' => ['.node', 'text'],
                    'node_value' => ['.node', 'href'],
                    'reply_count' => ['.count_livid', 'text'],
                ],
            ],
        ],['tabs','nodes','posts'])->map(function ($item) {
            return $item->all();
        })->all();
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

        $header = $crawler->parse([
            'title' => ['header h3','text'],
            'desc' => ['header p','text'],
        ]);

        $post = $crawler->group('.featured-collection')->parse([
            'title' => ['h3 a','text'],
            'link' => ['h3 a','href'],
            'desc' => ['.content','text'],
        ])/*->filter(function ($item) {
            return $item['title'] && $item['link'];
        })*/->values();

        dd($header,$post);
    }
}
