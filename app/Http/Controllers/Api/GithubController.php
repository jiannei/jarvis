<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CrawlerService;
use Illuminate\Http\Request;
use Jiannei\Response\Laravel\Support\Facades\Response;

class GithubController extends Controller
{
    public function __construct(private CrawlerService $crawlerService)
    {
    }

    public function trending(Request $request, ?string $language = null)
    {
        $this->validate($request, [
            'spoken_language_code' => 'nullable|string',
            'since' => 'nullable|string|in:daily,weekly,monthly',
        ]);

        $result = $this->crawlerService->handleGithubTrending($language, $request->only('spoken_language_code', 'since'));

        return Response::success($result);
    }

    public function languages()
    {
        $result = $this->crawlerService->handleGithubTrendingLanguages();

        return Response::success($result);
    }

    public function spokenLanguages()
    {
        $result = $this->crawlerService->handleGithubTrendingSpokenLanguages();

        return Response::success($result);
    }

    public function ruanyfWeeklyLatest(?int $period = null)
    {
        $result = $this->crawlerService->handleRuanyfWeeklyLatest($period);

        return Response::success($result);
    }

    public function ruanyfWeekly()
    {
        $result = $this->crawlerService->handleRuanyfWeekly();

        return Response::success($result);
    }
}
