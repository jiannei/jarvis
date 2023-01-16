<?php

namespace App\Listeners;

use App\Events\CrawlFinished;
use Illuminate\Support\Facades\Storage;
use League\HTMLToMarkdown\HtmlConverter;

class CrawlFinishedListener
{
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

        $markdown = $converter->convert($event->post['content']);

        // 存 markdown
        $path = parse_url($event->post['link'])['path'];
        Storage::put($event->channel.$path.'.md', $markdown);
    }
}
