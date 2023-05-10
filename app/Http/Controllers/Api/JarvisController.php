<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;
use Jiannei\Response\Laravel\Support\Facades\Response;

class JarvisController extends Controller
{
    public function crawl(Request $request, string $source)
    {
        try {
            $result = Crawler::json($source, $request->all());
        } catch (\Throwable $e) {
            abort(404, $e->getMessage());
        }

        return Response::success($result);
    }
}
