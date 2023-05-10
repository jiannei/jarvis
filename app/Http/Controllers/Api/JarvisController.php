<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;
use Jiannei\Response\Laravel\Support\Facades\Response;

class JarvisController extends Controller
{
    public function feeds(Request $request, string $source)
    {
        $result = Cache::remember('feeds:'.$source, now()->addMinute(), function () use ($source, $request) {
            try {
                return Crawler::json($source, $request->all());
            } catch (\Throwable $e) {
                abort(404, $e->getMessage());
            }
        });

        dispatch(new ActivityLog($source, $request->user(), ['ip' => $request->ip()], 'crawler'));

        return Response::success($result);
    }
}
