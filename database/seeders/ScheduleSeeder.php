<?php

namespace Database\Seeders;

use App\Models\Mongodb\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $schedules = [
            [
                'description' => '爬取 Github daily 趋势',
                'command' => 'github:trending',
                'parameters' => '--spoken_language_code=zh --since=daily',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
            ],
            [
                'description' => '爬取 Github daily 趋势',
                'command' => 'github:trending',
                'parameters' => '--language=php --spoken_language_code=zh --since=daily',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
            ],
            [
                'description' => '爬取 Github daily 趋势',
                'command' => 'github:trending',
                'parameters' => '--language=golang --spoken_language_code=zh --since=daily',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
            ],
            [
                'description' => '爬取 Github daily 趋势',
                'command' => 'github:trending',
                'parameters' => '--language=javascript --spoken_language_code=zh --since=daily',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
            ],
            [
                'description' => '爬取 Github daily 趋势',
                'command' => 'github:trending',
                'parameters' => '--language=vue --spoken_language_code=zh --since=daily',
                'expression' => '*/10 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
            ],
        ];

        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }

    }
}
