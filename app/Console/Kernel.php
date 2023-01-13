<?php

namespace App\Console;

use App\Console\Commands\GithubTrending;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(GithubTrending::class,['--spoken_language_code=zh --since=daily'])
            ->everyTenMinutes()
            ->runInBackground();

        $schedule->command(GithubTrending::class,['--language=php --spoken_language_code=zh --since=daily'])
            ->everyTenMinutes()
            ->runInBackground();

        $schedule->command(GithubTrending::class,['--language=golang --spoken_language_code=zh --since=daily'])
            ->everyTenMinutes()
            ->runInBackground();

        $schedule->command(GithubTrending::class,['--language=javascript --spoken_language_code=zh --since=daily'])
            ->everyTenMinutes()
            ->runInBackground();

        $schedule->command(GithubTrending::class,['--language=vue --spoken_language_code=zh --since=daily'])
            ->everyTenMinutes()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
