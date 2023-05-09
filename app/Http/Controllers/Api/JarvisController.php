<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Jiannei\LaravelCrawler\Support\Facades\Crawler;
use Jiannei\Response\Laravel\Support\Facades\Response;
use OpenAI\Laravel\Facades\OpenAI;

class JarvisController extends Controller
{
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
