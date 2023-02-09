<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
