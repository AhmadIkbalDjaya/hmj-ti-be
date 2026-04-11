<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Business;
use App\Models\Complaint;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
        Article::factory()->count(8)->create();
        Article::factory()->count(2)->featured()->create();

        // Seed Businesses
        Business::factory()->count(8)->create();
        Business::factory()->count(2)->inactive()->create();

        // Seed Complaints
        Complaint::factory()->count(15)->create();

        // Seed Positions (hierarchical structure)
        $this->seedPositions();
    }

    /**
     * Seed positions with a realistic organizational hierarchy and members.
     */
    private function seedPositions(): void
    {
        // Level 0: Presidium
        $ketua = Position::create([
            'name' => 'Ketua Umum',
            'slug' => 'ketua-umum',
            'parent_id' => null,
            'level' => 0,
            'order_index' => 0,
            'is_active' => true,
        ]);

        // Level 1: Wakil Ketua
        $wakil1 = Position::create([
            'name' => 'Wakil Ketua 1',
            'slug' => 'wakil-ketua-1',
            'parent_id' => $ketua->id,
            'level' => 1,
            'order_index' => 0,
            'is_active' => true,
        ]);

        $wakil2 = Position::create([
            'name' => 'Wakil Ketua 2',
            'slug' => 'wakil-ketua-2',
            'parent_id' => $ketua->id,
            'level' => 1,
            'order_index' => 1,
            'is_active' => true,
        ]);

        // Level 2: Ketua Bidang
        $bidangNames = [
            ['name' => 'Ketua Bidang Akademik', 'parent' => $wakil1],
            ['name' => 'Ketua Bidang Minat & Bakat', 'parent' => $wakil1],
            ['name' => 'Ketua Bidang Humas', 'parent' => $wakil2],
            ['name' => 'Ketua Bidang Kewirausahaan', 'parent' => $wakil2],
        ];

        $bidangs = [];
        foreach ($bidangNames as $index => $bidang) {
            $bidangs[] = Position::create([
                'name' => $bidang['name'],
                'slug' => Str::slug($bidang['name']),
                'parent_id' => $bidang['parent']->id,
                'level' => 2,
                'order_index' => $index,
                'is_active' => true,
            ]);
        }

        // Level 3: Anggota positions under each Bidang
        foreach ($bidangs as $bidang) {
            $anggotaCount = rand(2, 4);
            for ($i = 1; $i <= $anggotaCount; $i++) {
                $anggotaName = "Anggota " . Str::afterLast($bidang->name, 'Bidang ') . " {$i}";
                Position::create([
                    'name' => $anggotaName,
                    'slug' => Str::slug($anggotaName),
                    'parent_id' => $bidang->id,
                    'level' => 3,
                    'order_index' => $i - 1,
                    'is_active' => true,
                ]);
            }
        }

        // Create members for all positions
        $positions = Position::all();
        foreach ($positions as $position) {
            Member::factory()->create([
                'position_id' => $position->id,
            ]);
        }
    }
}
