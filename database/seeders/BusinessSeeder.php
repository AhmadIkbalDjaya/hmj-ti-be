<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Business::create([
            'title' => 'Jasa Titip Bayar SPP',
            'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias quos illo consequatur, officiis quas dolore!',
            'price' => '10.000',
            'isActive' => 1,
            'image' => 'sadsdafds.jpg',
        ]);
        Business::create([
            'title' => 'Jasa Titip Absen',
            'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias quos illo consequatur, officiis quas dolore!',
            'price' => '15.000',
            'isActive' => 0,
            'image' => 'sadsdafds.jpg',
        ]);
        Business::create([
            'title' => 'Jasa Joki Laporan',
            'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias quos illo consequatur, officiis quas dolore!',
            'price' => '40.000',
            'isActive' => 0,
            'image' => 'sadsdafds.jpg',
        ]);
        Business::create([
            'title' => 'Jasa Joki Tugas',
            'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Alias quos illo consequatur, officiis quas dolore!',
            'price' => '35.000',
            'isActive' => 1,
            'image' => 'sadsdafds.jpg',
        ]);
    }
}
