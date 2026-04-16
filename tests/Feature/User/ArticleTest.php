<?php

namespace Tests\Feature\User;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spectator\Spectator;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/user/articles';

    protected User $user;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('openapi.json');
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    protected function fakeStorage(): void
    {
        Storage::fake('public');
    }

    public function test_get_articles_success(): void
    {
        Article::factory()->count(3)->create();

        $response = $this->withToken($this->token)->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_get_articles_with_pagination(): void
    {
        Article::factory()->count(15)->create();

        $response = $this->withToken($this->token)->get($this->base_url.'?page=2&limit=5');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(5, 'data');
    }

    public function test_get_articles_with_search(): void
    {
        Article::factory()->create(['title' => 'Laravel Tutorial']);
        Article::factory()->create(['title' => 'Vue Guide', 'content' => 'Learn Laravel with Vue']);
        Article::factory()->create(['title' => 'React Guide']);

        $response = $this->withToken($this->token)->get($this->base_url.'?search=Laravel');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_articles_with_is_active_filter(): void
    {
        Article::factory()->count(2)->create(['is_active' => true]);
        Article::factory()->inactive()->count(3)->create();

        $response = $this->withToken($this->token)->get($this->base_url.'?is_active=1');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_articles_with_is_featured_filter(): void
    {
        Article::factory()->featured()->count(2)->create();
        Article::factory()->count(3)->create(['is_featured' => false]);

        $response = $this->withToken($this->token)->get($this->base_url.'?is_featured=1');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_articles_unauthenticated(): void
    {
        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    // -------------------------------------------------------------------------
    // Show Article
    // -------------------------------------------------------------------------

    public function test_show_article_success(): void
    {
        $article = Article::factory()->create();

        $response = $this->withToken($this->token)->get("{$this->base_url}/{$article->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.id', $article->id);
        $response->assertJsonPath('data.title', $article->title);
        $response->assertJsonPath('data.slug', $article->slug);
        $response->assertJsonPath('data.content', $article->content);
    }

    public function test_show_article_unauthenticated(): void
    {
        $article = Article::factory()->create();

        $response = $this->get("{$this->base_url}/{$article->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(401);
    }

    public function test_show_article_not_found(): void
    {
        $response = $this->withToken($this->token)->get("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }

    // -------------------------------------------------------------------------
    // Store Article
    // -------------------------------------------------------------------------

    public function test_store_article_success(): void
    {
        $this->fakeStorage();

        $payload = [
            'title' => 'New Article Title',
            'slug' => 'new-article-title',
            'publish_at' => '2026-04-11T00:00:00Z',
            'is_active' => true,
            'is_featured' => false,
            'content' => 'This is the article content.',
            'image' => UploadedFile::fake()->image('article.jpg'),
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(201);

        $response->assertJsonPath('data.title', $payload['title']);
        $response->assertJsonPath('data.slug', $payload['slug']);

        $this->assertDatabaseHas('articles', [
            'title' => $payload['title'],
            'slug' => $payload['slug'],
        ]);
    }

    public function test_store_article_unauthenticated(): void
    {
        $payload = [
            'title' => 'New Article Title',
            'slug' => 'new-article-title',
            'publish_at' => '2026-04-11T00:00:00Z',
            'is_active' => true,
            'is_featured' => false,
            'content' => 'This is the article content.',
            'image' => 'https://example.com/images/article.jpg',
        ];

        $response = $this->post($this->base_url, $payload);

        $response->assertValidResponse(401);
    }

    public function test_store_article_validation_fails_when_fields_missing(): void
    {
        $payload = [
            'title' => '',
            'slug' => '',
            'publish_at' => '',
            'is_active' => '',
            'is_featured' => '',
            'content' => '',
            'image' => '',
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors([
            'title', 'slug', 'publish_at', 'is_active', 'is_featured', 'content', 'image',
        ]);
    }

    public function test_store_article_validation_fails_when_publish_at_is_invalid(): void
    {
        $payload = [
            'title' => 'New Article Title',
            'slug' => 'new-article-title',
            'publish_at' => 'not-a-date',
            'is_active' => true,
            'is_featured' => false,
            'content' => 'This is the article content.',
            'image' => 'https://example.com/images/article.jpg',
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['publish_at']);
    }

    public function test_store_article_validation_fails_when_slug_is_duplicate(): void
    {
        Article::factory()->create(['slug' => 'existing-slug']);

        $payload = [
            'title' => 'Another Article',
            'slug' => 'existing-slug',
            'publish_at' => '2026-04-11T00:00:00Z',
            'is_active' => true,
            'is_featured' => false,
            'content' => 'Some content here.',
            'image' => 'https://example.com/images/article.jpg',
        ];

        $response = $this->withToken($this->token)
            ->post($this->base_url, $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['slug']);
    }

    // -------------------------------------------------------------------------
    // Update Article
    // -------------------------------------------------------------------------

    public function test_update_article_success(): void
    {
        $article = Article::factory()->create();

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'publish_at' => '2026-05-01T00:00:00Z',
            'is_active' => false,
            'is_featured' => true,
            'content' => 'Updated content.',
            'image' => null,
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$article->id}", $payload);

        $response->assertValidResponse(200);

        $response->assertJsonPath('data.title', 'Updated Title');
        $response->assertJsonPath('data.slug', 'updated-title');

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Updated Title',
            'slug' => 'updated-title',
        ]);
    }

    public function test_update_article_with_image(): void
    {
        $this->fakeStorage();

        $oldImage = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldImage->store('articles');

        $article = Article::factory()->create(['image' => $oldPath]);

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'publish_at' => '2026-05-01T00:00:00Z',
            'is_active' => true,
            'is_featured' => false,
            'content' => 'Updated content.',
            'image' => UploadedFile::fake()->image('new.jpg'),
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$article->id}", $payload);

        $response->assertValidResponse(200);

        Storage::assertMissing($oldPath);

        $article->refresh();
        Storage::assertExists($article->image);
    }

    public function test_update_article_unauthenticated(): void
    {
        $article = Article::factory()->create();

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'publish_at' => '2026-05-01T00:00:00Z',
            'is_active' => true,
            'is_featured' => false,
            'content' => 'Updated content.',
        ];

        $response = $this->put("{$this->base_url}/{$article->id}", $payload);

        $response->assertValidResponse(401);
    }

    public function test_update_article_not_found(): void
    {
        $payload = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'publish_at' => '2026-05-01T00:00:00Z',
            'is_active' => true,
            'is_featured' => false,
            'content' => 'Updated content.',
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/999", $payload);

        $response->assertValidResponse(404);
    }

    public function test_update_article_validation_fails_when_fields_missing(): void
    {
        $article = Article::factory()->create();

        $payload = [
            'title' => '',
            'slug' => '',
            'publish_at' => '',
            'is_active' => '',
            'is_featured' => '',
            'content' => '',
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$article->id}", $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors([
            'title', 'slug', 'publish_at', 'is_active', 'is_featured', 'content',
        ]);
    }

    public function test_update_article_validation_fails_when_publish_at_is_invalid(): void
    {
        $article = Article::factory()->create();

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'publish_at' => 'not-a-date',
            'is_active' => true,
            'is_featured' => false,
            'content' => 'Updated content.',
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$article->id}", $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['publish_at']);
    }

    public function test_update_article_validation_fails_when_slug_is_duplicate(): void
    {
        Article::factory()->create(['slug' => 'taken-slug']);
        $article = Article::factory()->create();

        $payload = [
            'title' => 'Updated Title',
            'slug' => 'taken-slug',
            'publish_at' => '2026-05-01T00:00:00Z',
            'is_active' => true,
            'is_featured' => false,
            'content' => 'Updated content.',
        ];

        $response = $this->withToken($this->token)
            ->put("{$this->base_url}/{$article->id}", $payload);

        $response->assertValidResponse(422);

        $response->assertJsonValidationErrors(['slug']);
    }

    // -------------------------------------------------------------------------
    // Destroy Article
    // -------------------------------------------------------------------------

    public function test_destroy_article_success(): void
    {
        $this->fakeStorage();

        $image = UploadedFile::fake()->image('article.jpg');
        $imagePath = $image->store('articles');

        $article = Article::factory()->create(['image' => $imagePath]);

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/{$article->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        Storage::assertMissing($imagePath);
    }

    public function test_destroy_article_without_image(): void
    {
        $article = Article::factory()->create(['image' => '']);

        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/{$article->id}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_destroy_article_unauthenticated(): void
    {
        $article = Article::factory()->create();

        $response = $this->deleteJson("{$this->base_url}/{$article->id}");

        $response->assertValidResponse(401);
    }

    public function test_destroy_article_not_found(): void
    {
        $response = $this->withToken($this->token)
            ->deleteJson("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }
}
