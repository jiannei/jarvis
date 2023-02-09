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
        $this->validate($request,[
            'model' => 'nullable|string',
            'prompt' => 'required|string'
        ]);

        $result = OpenAI::completions()->create([
            'model' => $request->get('model','text-davinci-003'),
            'prompt' => $request->get('prompt'),
        ]);

       return Response::success($result);
    }
}
