<?php

namespace Tests\Feature\Guest;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spectator\Spectator;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    protected $base_url = '/api/articles';

    protected function setUp(): void
    {
        parent::setUp();
        Spectator::using('openapi.json');
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);
    }

    // -------------------------------------------------------------------------
    // Index Articles (Public)
    // -------------------------------------------------------------------------

    public function test_get_articles_success(): void
    {
        Article::factory()->count(3)->create(['is_active' => true]);

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(3, 'data');
    }

    public function test_get_articles_only_returns_active(): void
    {
        Article::factory()->count(2)->create(['is_active' => true]);
        Article::factory()->inactive()->count(3)->create();

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_articles_with_pagination(): void
    {
        Article::factory()->count(15)->create(['is_active' => true]);

        $response = $this->get($this->base_url.'?page=2&limit=5');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(5, 'data');
    }

    public function test_get_articles_with_search(): void
    {
        Article::factory()->create(['title' => 'Laravel Tips', 'is_active' => true]);
        Article::factory()->create(['title' => 'Vue Guide', 'content' => 'Laravel integration', 'is_active' => true]);
        Article::factory()->create(['title' => 'React Tutorial', 'is_active' => true]);

        $response = $this->get($this->base_url.'?search=Laravel');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_articles_with_is_featured_filter(): void
    {
        Article::factory()->featured()->count(2)->create(['is_active' => true]);
        Article::factory()->count(3)->create(['is_active' => true, 'is_featured' => false]);

        $response = $this->get($this->base_url.'?is_featured=1');

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonCount(2, 'data');
    }

    public function test_get_articles_returns_correct_structure(): void
    {
        Article::factory()->create(['is_active' => true]);

        $response = $this->get($this->base_url);

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonStructure([
            'message',
            'data' => [
                '*' => ['id', 'title', 'slug', 'content', 'publish_at', 'image', 'is_featured'],
            ],
            'meta' => ['page', 'limit', 'total', 'total_page'],
        ]);
    }

    // -------------------------------------------------------------------------
    // Show Article (Public)
    // -------------------------------------------------------------------------

    public function test_show_article_success(): void
    {
        $article = Article::factory()->create(['is_active' => true]);

        $response = $this->get("{$this->base_url}/{$article->slug}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonPath('data.id', $article->id);
        $response->assertJsonPath('data.title', $article->title);
        $response->assertJsonPath('data.slug', $article->slug);
    }

    public function test_show_article_returns_correct_structure(): void
    {
        $article = Article::factory()->create(['is_active' => true]);

        $response = $this->get("{$this->base_url}/{$article->slug}");

        $response->assertValidRequest();
        $response->assertValidResponse(200);

        $response->assertJsonStructure([
            'message',
            'data' => ['id', 'slug', 'title', 'content', 'publish_at', 'image', 'is_active', 'is_featured'],
        ]);
    }

    public function test_show_article_not_found(): void
    {
        $response = $this->get("{$this->base_url}/999");

        $response->assertValidRequest();
        $response->assertValidResponse(404);
    }
}
