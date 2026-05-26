<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Business;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    /**
     * Seed the application's database for development.
     */
    public function run(): void
    {
        // Create default user
        User::create([
            'username' => 'admin',
            'email' => 'admin@hmjti.com',
            'password' => bcrypt('password'),
        ]);

        // Seed Articles
        Article::factory()->count(16)->create();
        Article::factory()->count(4)->featured()->create();

        // Seed Businesses
        Business::factory()->count(16)->create();
        Business::factory()->count(4)->inactive()->create();

        // Seed Complaints
        Complaint::factory()->count(15)->create();

        $this->call(OrganizationStructureSeeder::class);
        $this->call(OrganizationProfileSeeder::class);
    }
}
