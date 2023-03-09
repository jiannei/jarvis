<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;

class HomeController extends Controller
{
    public function __construct(private AuthService $service)
    {
    }

    public function home()
    {
        $token = Auth::check() ? $this->service->issueToken() : null;

        return view('app', compact('token'));
    }

    public function chat()
    {
//        $messages = collect(session('messages', []))->reject(fn ($message) => $message['role'] === 'system');
//
//        return view('chat', [
//            'messages' => $messages,
//        ]);
    }

    public function ai(Request $request): RedirectResponse
    {
//        // 系统消息
//        $messages = $request->session()->get('messages', [
//            ['role' => 'system', 'content' => 'You are A ChatGPT clone. Answer as concisely as possible.'],
//        ]);
//
//        // 用户消息
//        $messages[] = ['role' => 'user', 'content' => $request->input('message')];
//        $response = OpenAI::chat()->create([
//            'model' => 'gpt-3.5-turbo',
//            'messages' => $messages,
//        ]);
//
//        // 响应消息
//        $messages[] = ['role' => 'assistant', 'content' => $response->choices[0]->message->content];
//        $request->session()->put('messages', $messages);
//
//        return redirect('/chat');
    }
}
