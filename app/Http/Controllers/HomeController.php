<?php

namespace App\Http\Controllers;

use App\Enums\AbilityEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function home()
    {
        $token = null;
        if (Auth::check()) {
            $token = Cache::remember('auth:user:'.Auth::id(), now()->addSeconds(config('sanctum.expiration')), function () {
                return Auth::user()->createToken(config('app.name'),[AbilityEnum::SERVICE_API])->plainTextToken;
            });
        }

        return view('app', compact('token'));
    }
}
