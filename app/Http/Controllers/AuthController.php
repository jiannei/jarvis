<?php

namespace App\Http\Controllers;

use App\Providers\RouteServiceProvider;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private AuthService $service)
    {
    }

    public function login()
    {
        return $this->service->handleGithubRedirect();
    }

    public function callback()
    {
        $user = $this->service->handleGithubCallback();

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function logout()
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect(RouteServiceProvider::HOME);
    }
}
