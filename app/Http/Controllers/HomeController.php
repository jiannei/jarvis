<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function home()
    {
        $token = null;
        if (Auth::check()) {
            $token = Cache::remember('auth:user:'.Auth::id(), now()->addSeconds(config('sanctum.expiration')), function () {
                return Auth::user()->createToken(config('app.name'),['service:api'])->plainTextToken;
            });
        }

        return view('app', compact('token'));
    }
}
