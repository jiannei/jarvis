<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/auth/callback', [AuthController::class, 'callback']);
});

Route::middleware('auth')->group(function () {
    Route::delete('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/home', [HomeController::class, 'home'])->name('home');
Route::permanentRedirect('/', '/home');
