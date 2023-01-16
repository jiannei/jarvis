<?php

use App\Http\Controllers\Api\GithubController;
use App\Http\Controllers\Api\LaravelNewsController;
use App\Http\Controllers\JarvisController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('abilities:service:api')->group(function () {
        // github api
        Route::prefix('github')->group(function () {
            Route::get('languages', [GithubController::class, 'languages']);
            Route::get('spoken-languages', [GithubController::class, 'spokenLanguages']);
            Route::get('trending/{language?}', [GithubController::class, 'trending'])->whereAlpha('language');
        });

        // laravel-news
        Route::prefix('laravel-news')->group(function () {
            Route::get('blogs', [LaravelNewsController::class, 'blogs']);
            Route::get('blogs/{link}', [LaravelNewsController::class, 'blog']);
        });
    });

    Route::middleware('abilities:*')->group(function () {
        Route::get('database', [JarvisController::class, 'database']);
    });
});
