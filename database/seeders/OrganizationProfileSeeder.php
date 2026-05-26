<?php

namespace Database\Seeders;

use App\Models\OrganizationProfile;
use Illuminate\Database\Seeder;

class OrganizationProfileSeeder extends Seeder
{
    public function run(): void
    {
        OrganizationProfile::updateOrCreate(
            ['id' => 1],
            [
                'goal' => "Menjadikan wadah pengembangan teknologi yang unggul, melahirkan kader yang berkepribadian muslim, berakhlakul qarimah, cerdas, kompetitif, dan professional yang dijiwai Al-Qur'an dan Al-Hadist",
                'vision' => 'Terwujudnya HMJ-TI sebagai wadah pengembangan intelektual dan peningkatan sumber daya manusia serta peningkatan kreativitas bagi HMJ-TI FST UINAM',
                'missions' => [
                    'Mendorong mahasiswa aktif dalam kompetisi akademik dan mengembangkan program belajar bersama.',
                    'Memaksimalkan peran HMJ-TI sebagai fasilator pengembangan potensi mahasiswa melalui pembinaan kaderisasi dan organisasi.',
                    'Menciptakan ruang ekspresi kreatif melalui media publikasi, minat bakat dan lomba-lomba.',
                ],
                'main_image' => null,
                'secondary_image' => null,
            ],
        );
    }
}
