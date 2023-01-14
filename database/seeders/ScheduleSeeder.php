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
        Schedule::create([
            'description' => '爬取 Github daily 趋势',
            'command' => 'github:trending',
            'parameters' => 'all',
            'expression' => '*/10 * * * *',
            'active' => true,
            'timezone' => 'Asia/Shanghai',
            'in_background' => true,
            'in_maintenance_mode' => true,
            'output_file_path' => 'github_trending.log',
            'output_append' => true,
        ]);
    }
}
