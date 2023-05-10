<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        User::create([
            'name' => 'jarvis',
            'email' => 'jarvis@coderplanets.cn',
            'password' => bcrypt('hey.jarvis'),
        ]);
    }
}
