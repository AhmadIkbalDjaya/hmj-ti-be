<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Business;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OldDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedArticles();
        $this->seedBusinesses();
    }

    private function seedArticles(): void
    {
        $jsonPath = database_path('seeders/data/articles.json');
        $imageSourceDir = database_path('seeders/data/articles/');
        $targetDir = 'articles';

        if (! File::exists($jsonPath)) {
            $this->command->error("❌ Json file not found: $jsonPath");

            return;
        }

        $articles = json_decode(File::get($jsonPath), true);

        if (! is_array($articles)) {
            $this->command->error('❌ Json format not valid');

            return;
        }

        if (! Storage::exists($targetDir)) {
            Storage::makeDirectory($targetDir);
        }

        DB::transaction(function () use ($articles, $imageSourceDir, $targetDir) {
            foreach ($articles as $article) {
                $imageName = $article['image'] ?? null;
                $storedImagePath = null;

                if ($imageName && File::exists($imageSourceDir.$imageName)) {
                    $destination = $targetDir.'/'.$imageName;
                    Storage::put($destination, File::get($imageSourceDir.$imageName));

                    $storedImagePath = $destination;
                } else {
                    $this->command->warn("⚠️ Image not found: $imageName");
                }

                Article::create([
                    'title' => $article['title'],
                    'slug' => $article['slug'],
                    'content' => $article['content'],
                    'publish_at' => $article['publish_at'],
                    'image' => $storedImagePath,
                    'created_at' => $article['publish_at'],
                    'updated_at' => $article['publish_at'],
                ]);
            }
        });
    }

    private function seedBusinesses(): void
    {
        $jsonPath = database_path('seeders/data/businesses.json');
        $imageSourceDir = database_path('seeders/data/businesses/');
        $targetDir = 'businesses';

        if (! File::exists($jsonPath)) {
            $this->command->error("❌ Json file not found: $jsonPath");

            return;
        }

        $businesses = json_decode(File::get($jsonPath), true);

        if (! is_array($businesses)) {
            $this->command->error('❌ Json format not valid');

            return;
        }

        if (! Storage::exists($targetDir)) {
            Storage::makeDirectory($targetDir);
        }

        DB::transaction(function () use ($businesses, $imageSourceDir, $targetDir) {
            foreach ($businesses as $business) {
                $imageName = $business['image'] ?? null;
                $storedImagePath = null;

                if ($imageName && File::exists($imageSourceDir.$imageName)) {
                    $destination = $targetDir.'/'.$imageName;
                    Storage::put($destination, File::get($imageSourceDir.$imageName));

                    $storedImagePath = $destination;
                } else {
                    $this->command->warn("⚠️ Image not found: $imageName");
                }

                Business::create([
                    'slug' => Str::slug($business['title']),
                    'title' => $business['title'],
                    'description' => $business['description'],
                    'price' => 10000,
                    'image' => $storedImagePath,
                    'whatsapp' => $business['whatsapp'],
                    'created_at' => $business['publish_at'],
                    'updated_at' => $business['publish_at'],
                ]);
            }
        });
    }
}
