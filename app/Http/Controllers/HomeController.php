<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;

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
}
