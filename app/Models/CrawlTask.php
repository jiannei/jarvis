<?php

namespace App\Models;

class CrawlTask extends \Jiannei\LaravelCrawler\Models\CrawlTask
{
    public function toggle()
    {
        $this->active = ! $this->active;
        $this->save();
    }
}
