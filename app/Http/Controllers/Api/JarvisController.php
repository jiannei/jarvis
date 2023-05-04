<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Github\TrendingDaily;
use App\Models\Github\TrendingMonthly;
use App\Models\Github\TrendingWeekly;
use App\Models\Post;
use App\Services\CrawlerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Jiannei\Response\Laravel\Support\Facades\Response;
use OpenAI\Laravel\Facades\OpenAI;

class JarvisController extends Controller
{
    public function database()
    {
        return Storage::download('database.sqlite');
    }

    public function openAi(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $messages = Cache::get('open-ai:chat', [
            ['role' => 'system', 'content' => 'You are A ChatGPT clone. Answer as concisely as possible.'],
        ]);

        $messages[] = ['role' => 'user', 'content' => $request->get('message')];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
        ]);

        $messages[] = ['role' => 'assistant', 'content' => $response->choices[0]->message->content];

        Cache::put('open-ai:chat', $messages);

        return Response::success($response);
    }

    public function posts(Request $request)
    {
        $this->validate($request, [
            'source' => 'filled|string',
        ]);

        $posts = Post::query()
            ->select([
                'id',
                'title',
                'link',
                'category',
                'author',
                'source',
                'created_at',
                'updated_at',
            ])
            ->orderByDesc('updated_at')
            ->where($request->only('source'))
            ->paginate();

        return Response::success($posts);
    }

    public function crawl(Request $request, CrawlerService $service)
    {
        $this->validate($request, [
            'key' => 'required|string', // todo pref
        ]);

        $result = $service->handleCrawl($request->get('key'), $request->except('url'));

        return Response::success($result);
    }

    public function feed(Request $request)
    {
        $this->validate($request, [
            'limit' => 'integer',
            'page' => 'integer',
        ]);

        $posts = Post::query()->orderBy('id')->simplePaginate(
            perPage: $request->get('limit', 15),
            page: $request->get('page', 1)
        );

        return Response::success($posts);
    }

    public function trending(Request $request, $type)
    {
        $this->validate($request, [
            'period' => 'required',
        ]);

        $period = $request->get('period');

        $trending = match ($type) {
            'weekly' => TrendingWeekly::query()->orderBy('id')
                ->where('week', $period)
                ->get(),
            'monthly' => TrendingMonthly::query()->orderBy('id')
                ->where('month', $period)
                ->get(),
            default => TrendingDaily::query()->orderBy('id')
                ->where('day', $period)
                ->get(),
        };

        return Response::success($trending);
    }
}
