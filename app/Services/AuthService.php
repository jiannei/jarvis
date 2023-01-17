<?php

namespace App\Services;

use App\Enums\AbilityEnum;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;

class AuthService extends Service
{
    public function handleGithubRedirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGithubCallback()
    {
        $githubUser = Socialite::driver('github')->user();

        return User::updateOrCreate([
            'github_id' => $githubUser->id,
        ], [
            'name' => $githubUser->nickname,
            'nickname' => $githubUser->name,
            'bio' => $githubUser['bio'],
            'email' => $githubUser->email,
            'github_token' => $githubUser->token,
            'github_refresh_token' => $githubUser->refreshToken,
        ]);
    }

    public function issueToken()
    {
        return Cache::remember('auth:user:'.Auth::id(), now()->addSeconds(config('sanctum.expiration'))->subMinutes(rand(3,5)), function () {
            return Auth::user()->createToken(config('app.name'), [AbilityEnum::SERVICE_API])->plainTextToken;
        });
    }
}
