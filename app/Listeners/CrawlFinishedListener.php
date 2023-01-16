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

        $markdown = $converter->convert($event->content);

        // 存 markdown
        Storage::put($event->channel.DIRECTORY_SEPARATOR.$event->link.'.md', $markdown);

        //
    }
}
