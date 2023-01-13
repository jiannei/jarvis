<?php

namespace App\Http\Controllers;

use App\Services\CrawlerService;
use Illuminate\Http\Request;
use Jiannei\Response\Laravel\Support\Facades\Response;

class CrawlerController extends Controller
{
    public function __construct(private CrawlerService $service)
    {

    }

    public function fetch(Request $request)
    {
        $this->validate($request,[
            'url' => 'required|url',
            'rules' => 'required|array'
        ]);

        $result = $this->service->fetch($request->get('url'),$request->get('rules'));

        return Response::success($result);
    }
}
