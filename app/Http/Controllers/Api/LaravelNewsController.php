<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CrawlerService;
use Jiannei\Response\Laravel\Support\Facades\Response;

class LaravelNewsController extends Controller
{
    public function __construct(private readonly CrawlerService $service)
    {
    }

    public function blogs()
    {
        $blogs = $this->service->handleCrawl('laravel-news:blog');

        return Response::success($blogs);
    }

    public function blog($link)
    {
        $blog = $this->service->handleLaravelNewsBlog($link);

        return Response::success($blog);
    }
}
