<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RssService;
use Jiannei\Response\Laravel\Support\Facades\Response;

class RssController extends Controller
{
    public function __construct(private RssService $service)
    {

    }

    public function ruanyfWeekly()
    {
        $result = $this->service->handleRuanyfWeekly();

        return Response::success($result);
    }
}
