<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class IssueToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:issue-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Issue user token.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Auth::loginUsingId(1);

        $token = Cache::remember(
            'auth:user:'.Auth::id(),
            now()->addSeconds(config('sanctum.expiration'))->subMinutes(rand(3, 5)),
            function () {
                return Auth::user()->createToken(config('app.name'))->plainTextToken;
            }
        );

        $this->comment($token);
    }
}
