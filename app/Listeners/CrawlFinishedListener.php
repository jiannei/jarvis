<?php

namespace App\Listeners;

use App\Events\CrawlFinished;
use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
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
            $images = Crawler::new($event->post['content'])->filter('img')->attrs('src');

            $content = (new HtmlConverter())->convert($event->post['content']);
        } else {
            $images = Crawler::new(Str::markdown($event->post['content']))->filter('img')->attrs('src');

            $content = $event->post['content'];
        }

        $data = [
            'title' => $event->post['title'],
            'author' => $event->post['author']['name'],
            'content' => $content,
            'channel' => $event->channel,
            'link' => $event->post['link'],
            'category' => $event->post['category']['name'],
            'published_at' => $event->post['published_at'],
        ];

        $post = Post::query()->updateOrCreate(['link' => $event->post['link']], $data);

        $content = $post->content;
        $post->clearMediaCollection();
        foreach ($images as $image) {
            $media = $post->addMediaFromUrl($image)->toMediaCollection();
            $content = Str::replace($image, $media->getUrl(), $content);
        }

        $post->content = $content;
        $post->save();
    }
}
