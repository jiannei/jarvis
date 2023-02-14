<?php

namespace App\Listeners;

use App\Events\CrawlFinished;
use App\Models\Post;
use App\Notifications\FeedUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;
use League\HTMLToMarkdown\HtmlConverter;

class CrawlFinishedListener implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'listeners';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\CrawlFinished  $event
     * @return void
     */
    public function handle(CrawlFinished $event)
    {
        if ($event->contentType === 'html') {
            $images = Crawler::new($event->post['description'])->filter('img')->attrs('src');

            $content = (new HtmlConverter())->convert($event->post['description']);
        } else {
            $images = Crawler::new(Str::markdown($event->post['description']))->filter('img')->attrs('src');

            $content = $event->post['description'];
        }

        $data = [
            'title' => $event->post['title'],
            'link' => $event->post['link'],
            'description' => $content,
            'author' => $event->post['author']['name'],
            'category' => $event->post['category']['name'],
            'publish_date' => $event->post['publishDate'],
            'source' => $event->source,
        ];

        $post = Post::query()->updateOrCreate(['link' => $event->post['link']], $data);

        // todo 图片是否都需要存下来？比如 探索模块，直接链接到
        if (in_array($event->source, ['github', 'laravel-news','zhangxinxu','jspang'])) {
            $content = $post->description;
            $post->clearMediaCollection();
            foreach ($images as $image) {
                if (!Str::contains($image,['https','http'])) {
                    $image = Str::start($image,'https:');
                }

                $media = $post->addMediaFromUrl($image)->toMediaCollection();
                $content = Str::replace($image, $media->getUrl(), $content);
            }
        }

        $post->description = $content;
        $post->save();

        // todo
        // Notification::send(Auth::user(), new FeedUpdated($post));
    }
}
