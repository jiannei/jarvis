<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Jiannei\Response\Laravel\Support\Facades\Response;
use OpenAI\Laravel\Facades\OpenAI;

class JarvisController extends Controller
{
    public function database()
    {
        return Storage::download('database.sqlite');
    }

    public function openAi()
    {
        $result = OpenAI::completions()->create([
            'model' => 'text-davinci-003',
            'prompt' => 'PHP is',
        ]);

       return Response::success($result);
    }
}
