<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CrawlerService;
use Jiannei\Response\Laravel\Support\Facades\Response;

class LaravelNewsController extends Controller
{
    public function __construct(private CrawlerService $service)
    {
    }

    public function blogs()
    {
        $blogs = $this->service->handleLaravelNewsBlogs();

        return Response::success($blogs);
    }

    public function blog($link)
    {
        $blog = $this->service->handleLaravelNewsBlog($link);

        return Response::success($blog);
    }
}
