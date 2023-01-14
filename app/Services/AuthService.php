<?php

namespace App\Services;

use App\Models\User;
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
}
