<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Jiannei\Schedule\Laravel\Repositories\Models\Schedule;

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
                'description' => '执行爬取任务',
                'command' => 'crawler:task',
                'parameters' => '',
                'expression' => '*/5 * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'crawler_task.log',
                'output_append' => true,
            ],
            [
                'description' => '消费爬取数据',
                'command' => 'crawler:record',
                'parameters' => '',
                'expression' => '* * * * *',
                'active' => true,
                'timezone' => 'Asia/Shanghai',
                'in_background' => true,
                'in_maintenance_mode' => true,
                'output_file_path' => 'crawler_record.log',
                'output_append' => true,
            ],
        ];

        Schedule::truncate();
        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}
