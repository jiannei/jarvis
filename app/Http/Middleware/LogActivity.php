<?php

namespace App\Http\Middleware;

use App\Jobs\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        // sleep(5);

        $properties = [
            'request' => [
                'method' => $request->method(),
                'url' => $request->url(),
                'parameters' => $request->all(),
            ],
            'response' => json_decode($response->getContent(), true),
            'duration' => format_duration(microtime(true) - LARAVEL_START),
        ];

        ActivityLog::dispatch('', Auth::user(), $properties, 'api');
    }
}
