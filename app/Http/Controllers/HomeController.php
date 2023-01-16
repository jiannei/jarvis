<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct(private AuthService $service)
    {
    }

    public function home()
    {
        dd(Post::find(1)->getMedia()[1]->getUrl());

        $token = Auth::check() ? $this->service->issueToken() : null;

        return view('app', compact('token'));
    }
}
