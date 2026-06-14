<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@cash-tracker.local',
            'password' => bcrypt('admin123'),
            'permissions' => null, // admin = full akses
        ]);
    }
}
