<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
            'message' => 'required|string'
        ]);

        $messages = Cache::get('open-ai:chat',[
            ['role' => 'system', 'content' => 'You are A ChatGPT clone. Answer as concisely as possible.']
        ]);

        $messages[] = ['role' => 'user', 'content' => $request->get('message')];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages
        ]);

        $messages[] = ['role' => 'assistant', 'content' => $response->choices[0]->message->content];

        Cache::put('open-ai:chat',$messages);

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
}
