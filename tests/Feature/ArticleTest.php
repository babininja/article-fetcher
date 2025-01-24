<?php

namespace Tests\Feature;

use App\Models\Article\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_article_index_successful_response(): void
    {
        $response = $this->get('/api/v1/admin/articles');

        $response->assertOk();
    }


    /**
     * A basic test example.
     */
    public function test_article_show_successful_response(): void
    {
        $article = Article::factory()->create();
        $response = $this->get('/api/v1/admin/articles/'.$article->id);

        $response->assertOk();
    }


    /**
     * A basic test example.
     */
    public function test_article_show_notfound_response(): void
    {
        $response = $this->get('/api/v1/admin/articles/0');

        $response->assertStatus(404);
    }
}
