<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CrawlerService;
use Illuminate\Support\Carbon;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;
use Jiannei\Response\Laravel\Support\Facades\Response;

class LaravelNewsController extends Controller
{
    public function __construct(private CrawlerService $service)
    {
    }

    public function blog()
    {
        $blog = $this->service->handleLaravelNewsBlog();

        return Response::success($blog);
    }
}
