<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationStructureSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // LEVEL 0: PRESIDIUM INTI (parent_id: null)
        // ==========================================
        $presidiumRoles = [
            'Ketua Umum',
            'Sekretaris Umum',
            'Wakil Sekretaris Umum',
            'Bendahara Umum',
            'Wakil Bendahara Umum',
        ];

        foreach ($presidiumRoles as $index => $role) {
            Position::create([
                'name' => $role,
                'slug' => Str::slug($role),
                'parent_id' => null,
                'level' => 0,
                'order_index' => $index, // Penting untuk urutan tampilan
                'is_active' => true,
            ]);
        }

        // ==========================================
        // LEVEL 1: WAKIL KETUA (parent_id: null)
        // ==========================================
        $wakil1 = Position::create([
            'name' => 'Wakil Ketua 1',
            'slug' => 'wakil-ketua-1',
            'parent_id' => null,
            'level' => 1,
            'order_index' => 0,
            'is_active' => true,
        ]);

        $wakil2 = Position::create([
            'name' => 'Wakil Ketua 2',
            'slug' => 'wakil-ketua-2',
            'parent_id' => null,
            'level' => 1,
            'order_index' => 1,
            'is_active' => true,
        ]);

        // ==========================================
        // LEVEL 2+: BIDANG & ANGGOTA (parent_id: Wakil Ketua)
        // ==========================================
        $bidangWakil1 = ['Keorganisasian dan Kaderisasi', 'Keilmuan', 'Minat dan Bakat'];
        foreach ($bidangWakil1 as $index => $nama) {
            $this->createBidang($nama, $wakil1, $index);
        }

        $bidangWakil2 = ['Komunikasi dan Informasi', 'Humas dan Advokasi', 'Ekonomi Kreatif'];
        foreach ($bidangWakil2 as $index => $nama) {
            $this->createBidang($nama, $wakil2, $index);
        }

        // Assign Members
        $this->assignMembers();
    }

    private function createBidang(string $namaBidang, Position $wakil, int $order): void
    {
        // Level 2: Bidang (sebagai wadah/departemen)
        $bidang = Position::create([
            'name' => "Bidang $namaBidang",
            'slug' => 'bidang-'.Str::slug($namaBidang),
            'parent_id' => $wakil->id, // ✅ Menempel ke Wakil Ketua
            'level' => 2,
            'order_index' => $order,
        ]);

        // Level 3: Ketua Bidang (1 orang)
        Position::create([
            'name' => 'Ketua Bidang',
            'slug' => $bidang->slug.'-ketua',
            'parent_id' => $bidang->id,
            'level' => 3,
            'order_index' => 0,
        ]);

        // Level 4: Anggota (banyak orang)
        Position::create([
            'name' => 'Anggota',
            'slug' => $bidang->slug.'-anggota',
            'parent_id' => $bidang->id,
            'level' => 4,
            'order_index' => 1,
        ]);
    }

    private function assignMembers(): void
    {
        $positions = Position::all();

        foreach ($positions as $position) {
            // Presidium & Wakil Ketua: 1 orang
            if (in_array($position->level, [0, 1]) || str_contains($position->name, 'Ketua Bidang')) {
                Member::factory()->create([
                    'position_id' => $position->id,
                    // 'is_active' => true,
                ]);
            }
            // Anggota: 3-8 orang per bidang
            elseif ($position->name === 'Anggota') {
                Member::factory()->count(rand(3, 8))->create([
                    'position_id' => $position->id,
                    // 'is_active' => true,
                ]);
            }
        }
    }
}
