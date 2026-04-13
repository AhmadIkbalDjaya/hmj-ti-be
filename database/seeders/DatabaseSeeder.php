<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'username' => 'user1',
            'email' => 'user1@gmail.com',
            'password' => bcrypt('password'),
        ]);
    }
}
