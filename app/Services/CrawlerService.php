<?php

namespace App\Services;

use App\Enums\CrawlEnum;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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
            'publishDate' => Carbon::createFromTimestamp(strtotime($publishedAt))->format(CarbonInterface::DEFAULT_TO_STRING_FORMAT),
            'link' => CrawlEnum::LARAVEL_NEWS."/{$link}",
        ];
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

        $items = $crawler->filter('article ul li')->rules([
            'path' => ['a', 'href'],
            'title' => ['a', 'text'],
        ]);

        return compact('channel', 'items');
    }

    public function handleIndependentBlogs()
    {
        $crawler = Crawler::fetch('https://api.github.com/repos/timqian/chinese-independent-blogs/readme', null, [
            'headers' => [
                'Accept' => 'application/vnd.github.html+json',
                'Authorization' => 'Bearer '.Auth::user()->github_token,
            ],
        ]);

        $table = $crawler->filter('table');

//        $columns = [
//            'intro' => $table->filter('thead th:nth-child(2)')->text(),
//            'link' => $table->filter('thead th:nth-child(3)')->text(),
//            'tags' => $table->filter('thead th:nth-child(4)')->text(),
//        ];

//        $columns = $table->filter('thead th:nth-of-type(n+2)')->texts();

        $channel = [
            'link' => 'https://github.com/timqian/chinese-independent-blogs',
            'author' => 'https://github.com/timqian',
        ];

        $items = $table->filter('tbody tr')->rules([
            'intro' => ['td:nth-child(2)', 'text'],
            'link' => ['td:nth-child(3)', 'text'],
            'tags' => ['td:nth-child(4)', 'text'],
        ]);

        return compact('channel', 'items');
    }

    public function handleV2ex($tab = null)
    {
        $url = 'https://www.v2ex.com/';

        $crawler = Crawler::fetch($tab ? $url."?tab={$tab}" : $url);

        $tabs = $crawler->filter('#Tabs a')->rules([
            'label' => ['a', 'text'],
            'value' => ['a', 'href'],
        ]);

        // 可使用 API 获取
        $nodes = $crawler->filter('#SecondaryTabs a')->rules([
            'label' => ['a', 'text'],
            'value' => ['a', 'href'],
        ]);

        $posts = $crawler->filter('div .item table')->rules([
            'member_avatar' => ['.avatar', 'src'],
            'member_link' => ['strong a', 'href'],
            'member_name' => ['strong a', 'text'],
            'title' => ['.topic-link', 'text'],
            'link' => ['.topic-link', 'href'],
            'node_label' => ['.node', 'text'],
            'node_value' => ['.node', 'href'],
            'reply_count' => ['.count_livid', 'text'],
        ]);

        return compact('tabs', 'nodes', 'posts');
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

        $topics = $crawler->filter('.topic-list .simple-topic')->rules([
            'avatar' => ['.user-avatar img', 'src'],
            'category' => ['.category-name', 'text'],
            'title' => ['.topic-title', 'text'],
            'link' => ['.user-avatar a', 'href'],
            'replies' => ['.count_of_replies', 'text'],
            'updated_at' => ['.timeago', 'title'],
        ]);

        return collect($topics)->filter(function ($topic) {
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

        if (Str::contains($category, '博客')) {
            $author = $crawler->filter('.blog-article .content .header')->text();
        } else {
            $author = $crawler->filter('.authors-box .content .header')->text();
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
        $blogs = $crawler->filter('.blog-list .blog-item')->rules([
            'title' => ['.item-title', 'text'],
            'link' => ['.item-title a', 'href'],
            'category' => ['.item-tag span', 'text', 1],
            'publishDate' => ['.item-tag span', 'text', 0],
            'views' => ['.item-tag span', 'text', 2],
            'summary' => ['.item-desc', 'text'],
        ]);

        $videos = $crawler->filter('.left-video-box li')->rules([
            'link' => ['a', 'href'],
            'cover' => ['.video-image img', 'src'],
            'title' => ['.video-title', 'text'],
        ]);

        return compact('blogs', 'videos');
    }

    public function handleJspangPost($link)
    {
        $crawler = Crawler::fetch('https://jspang.com'.$link, null, ['verify' => false]);

        $post = $crawler->filter('.main-box')->rules([
            'title' => ['.article-title h1', 'text'],
            'category' => ['.remarks span b', 'text', 0],
            'publishDate' => ['.remarks span b', 'text', 1],
            'videos' => ['.remarks span b', 'text', 2],
            'views' => ['.remarks span b', 'text', 3],
            'time' => ['.remarks span b', 'text', 4],
            'summary' => ['.introduce-html', 'text'],
            'description' => ['.article-details', 'html'],
        ]);

        $post = current($post); // FIXED me

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

        // todo dot => undot
//        $posts = $crawler->filter('.entry-list > .item')->rules([
//            'author.name' => ['.user-message a','text'],
//            'author.link' => ['.user-message a','href'],
//        ]);

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
        $categories = $crawler->filter('.main-content .indexbox:nth-of-type(n+2)')->rules([
            'title' => ['.indexbox_title strong', 'text'],
        ]);

        $data = [];
        foreach ($categories as $index => $category) {
            $pos = 2 * ($index + 1) + 1;
            $links = $crawler->filter(".main-content .indexbox:nth-of-type({$pos}) li")->rules([
                'icon' => ['img', 'src'],
                'link' => ['a', 'href'],
                'description' => ['p', 'text'],
            ]);

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
        return $crawler->filter('.item')->rules([
            'title' => ['.topic-link', 'text'],
            'link' => ['.topic-link', 'href'],
            'node_name' => ['.node', 'text'],
            'node_link' => ['.node', 'href'],
            'member_name' => ['strong', 'text'],
            'member_link' => ['strong a', 'href'],
        ]);
    }
}
