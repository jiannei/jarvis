<?php

use App\Enums\AbilityEnum;
use App\Http\Controllers\Api\GithubController;
use App\Http\Controllers\Api\JarvisController;
use App\Http\Controllers\Api\LaravelNewsController;
use App\Http\Controllers\Api\RssController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('abilities:'.AbilityEnum::SERVICE_API)->group(function () {
        // github api
        Route::prefix('github')->group(function () {
            Route::get('languages', [GithubController::class, 'languages']);
            Route::get('spoken-languages', [GithubController::class, 'spokenLanguages']);
            Route::get('trending/{language?}', [GithubController::class, 'trending'])->whereAlpha('language');

            Route::get('ruanyf/weekly', [GithubController::class, 'ruanyfWeekly']);
            Route::get('ruanyf/weekly/{period?}', [GithubController::class, 'ruanyfWeeklyLatest'])->whereNumber('period');

            Route::get('independent-blogs', [GithubController::class, 'independentBlogs']);
        });

        // laravel-news
        Route::prefix('laravel-news')->group(function () {
            Route::get('blogs', [LaravelNewsController::class, 'blogs']);
            Route::get('blogs/{link}', [LaravelNewsController::class, 'blog']);
        });

        // Rss
        Route::get('rss/ruanyf/weekly', [RssController::class, 'ruanyfWeekly']);
        Route::get('rss/zhangxinxu/blog', [RssController::class, 'zhangxinxuBlog']);
    });

    Route::middleware('abilities:'.AbilityEnum::ALL)->group(function () {
        Route::get('database', [JarvisController::class, 'database']);

        Route::post('openai', [JarvisController::class, 'openAi']);

        Route::get('posts', [JarvisController::class, 'posts']);

        Route::get('crawl', [JarvisController::class, 'crawl']);

        Route::get('feed', [JarvisController::class, 'feed']);

        Route::get('trending/{type}', [JarvisController::class, 'trending']);
    });
});
