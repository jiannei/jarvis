<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
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
        $result = OpenAI::completions()->create($request->all());

        return Response::success($result);
    }

    public function posts()
    {
        $posts = Post::select(['id','title','link','category','author'])
            ->orderByDesc('updated_at')
            ->paginate();

        return Response::success($posts);
    }
}
