<?php

namespace App\Listeners;

use App\Events\CrawlFinished;
use App\Models\Post;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
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
        $converter = new HtmlConverter();

        $data = [
            'title' => $event->post['title'],
            'author' => $event->post['author']['name'],
            'content' => $event->contentType === 'html' ? $converter->convert($event->post['content']) : $event->post['content'],
            'channel' => $event->channel,
            'link' => $event->post['link'],
            'category' => $event->post['category']['name'],
            'published_at' => $event->post['published_at'],
        ];

        $post = Post::query()->updateOrCreate(['link' => $event->post['link']], $data);

        $content = $post->content;
        foreach ($event->post['images'] as $image) {
            $media = $post->addMediaFromUrl($image)->toMediaCollection();
            $content = Str::replace($image, $media->getUrl(), $content);
        }

        $post->content = $content;
        $post->save();
    }
}
