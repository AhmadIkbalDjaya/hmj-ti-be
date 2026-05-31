<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use JsonException;

class OldOrganizationStructureSeeder extends Seeder
{
    private const MEMBER_SOURCE_DIR = 'seeders/data/members';

    private const MEMBER_STORAGE_DIR = 'members';

    /**
     * @var array<string, bool>
     */
    private array $usedSlugs = [];

    public function run(): void
    {
        $jsonPath = database_path('seeders/data/organization-structure.json');

        if (! File::exists($jsonPath)) {
            $this->command->error("Json file not found: $jsonPath");

            return;
        }

        try {
            $data = json_decode(File::get($jsonPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->command->error('Json format not valid: '.$exception->getMessage());

            return;
        }

        if (! is_array($data) || ! isset($data['position']) || ! is_array($data['position'])) {
            $this->command->error('Json format not valid: missing position array');

            return;
        }

        $this->usedSlugs = [];

        DB::transaction(function () use ($data) {
            $this->seedPositions($data['position']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $positions
     */
    private function seedPositions(array $positions, ?Position $parent = null): void
    {
        foreach ($positions as $positionData) {
            $position = Position::create([
                'name' => $positionData['name'],
                'slug' => $this->uniqueSlug($positionData['slug'], $parent?->slug),
                'parent_id' => $parent?->id,
                'level' => $positionData['level'] ?? 0,
                'order_index' => $positionData['order_index'] ?? 0,
                'is_active' => $positionData['is_active'] ?? true,
            ]);

            foreach ($positionData['members'] ?? [] as $memberData) {
                Member::create([
                    'name' => $memberData['name'],
                    'photo' => $this->storeMemberPhoto($memberData['photo'] ?? null),
                    'position_id' => $position->id,
                ]);
            }

            if (isset($positionData['children']) && is_array($positionData['children'])) {
                $this->seedPositions($positionData['children'], $position);
            }
        }
    }

    private function uniqueSlug(string $slug, ?string $parentSlug = null): string
    {
        $baseSlug = isset($this->usedSlugs[$slug]) && $parentSlug
            ? "{$parentSlug}-{$slug}"
            : $slug;

        $candidate = $baseSlug;
        $suffix = 2;

        while (isset($this->usedSlugs[$candidate])) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $this->usedSlugs[$candidate] = true;

        return $candidate;
    }

    private function storeMemberPhoto(?string $photo): ?string
    {
        if (! $photo) {
            return null;
        }

        $sourcePath = $this->memberPhotoSourcePath($photo);

        if (! $sourcePath) {
            $this->command->warn("Member photo not found: {$photo}");

            return null;
        }

        if (! Storage::exists(self::MEMBER_STORAGE_DIR)) {
            Storage::makeDirectory(self::MEMBER_STORAGE_DIR);
        }

        $destination = self::MEMBER_STORAGE_DIR.'/'.basename($sourcePath);
        Storage::put($destination, File::get($sourcePath));

        return $destination;
    }

    private function memberPhotoSourcePath(string $photo): ?string
    {
        $fileName = basename($photo);
        $sourceDir = database_path(self::MEMBER_SOURCE_DIR);
        $candidates = [$fileName];

        if (! pathinfo($fileName, PATHINFO_EXTENSION)) {
            $candidates[] = "{$fileName}.webp";
        }

        foreach ($candidates as $candidate) {
            $sourcePath = "{$sourceDir}/{$candidate}";

            if (File::exists($sourcePath)) {
                return $sourcePath;
            }
        }

        return null;
    }
}
