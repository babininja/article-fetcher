<?php

namespace App\Services\FetchArticle\Models;

use App\Enums\Fetch\Provider;
use App\Services\FetchArticle\FetchArticleAbstract;
use Carbon\Carbon;

class NewsAPINewsService extends FetchArticleAbstract
{
    protected string $apiKey;
    public string $method = 'get';

    public function __construct()
    {
        $this->path = config('services.newsapi.base_url') . "/everything";
        $this->apiKey = config('services.newsapi.key');
    }

    /**
     * @param array $articles
     * @return array
     */
    public function mapToStandardFormat(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'source' => $article['source']['name'] ?? 'Unknown',
                'provider' => Provider::NEWSAPI,
                'author' => $article['author'] ?? 'Unknown',
                'title' => $article['title'],
                'content' => $article['content'] ?? '',
                'category' => 'General', // NewsAPI does not categorize by section
                'published_at' => Carbon::parse($article['publishedAt']),
            ];
        }, $articles);
    }

    /**
     * @param string|null $from
     * @param string|null $to
     * @return int[]
     */
    public function getParams(?string $from = null, ?string $to = null): array
    {
        $params = [
            'pageSize' => $this->limit,
            'page' => $this->page,
            'q' => 'latest'
        ];

        if ($from) {
            $params['from'] = $from;
        }
        if ($to) {
            $params['to'] = $to;
        }

        return $params;
    }

    /**
     * @return int
     */
    public function getFirstPage(): int
    {
        return 1;
    }

    /**
     * @param array $response
     * @return bool
     */
    public function hasMorePage(array $response): bool
    {
        $totalPages = $response['totalResults'] / $this->limit;
        return $this->page <= $totalPages;
    }

    /**
     * @param array $response
     * @return array
     */
    public function getResult(array $response): array
    {
        return $response['articles'];
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];
    }
}
