<?php

namespace App\Services\FetchArticle\Models;

use App\Enums\Fetch\Provider;
use App\Services\FetchArticle\FetchArticleAbstract;
use Carbon\Carbon;

class NYTNewsService extends FetchArticleAbstract
{
    protected string $apiKey;
    public string $method = 'get';

    public function __construct()
    {
        $this->path = config('services.nyt.base_url') . "/search/v2/articlesearch.json";
        $this->apiKey = config('services.nyt.key');
    }

    /**
     * @param array $articles
     * @return array
     */
    public function mapToStandardFormat(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'source' => 'The New York Times',
                'provider' => Provider::NYT,
                'author' => $article['byline']['original'] ?? 'Unknown',
                'title' => $article['headline']['main'] ?? 'Unknown',
                'content' => $article['abstract'] ?? '',
                'category' => $article['section'] ?? 'Unknown',
                'published_at' => Carbon::parse($article['pub_date']),
            ];
        }, $articles);
    }

    /**
     * @param string|null $from
     * @param string|null $to
     * @return array
     */
    public function getParams(?string $from = null, ?string $to = null): array
    {
        $params = [
            'api-key' => $this->apiKey,
            'page' => $this->page,
        ];

        if ($from) {
            $params['begin_date'] = date('Ymd', strtotime($from));
        }
        if ($to) {
            $params['end_date'] = date('Ymd', strtotime($to));
        }

        return $params;
    }

    /**
     * @return int
     */
    public function getFirstPage(): int
    {
        return 0;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * @param array $response
     * @return bool
     */
    public function hasMorePage(array $response): bool
    {
        // nyt has default pagination of 10 items ant it cant be modified
        return count($response['response']['docs']) < 10;
    }

    /**
     * @param array $response
     * @return array
     */
    public function getResult(array $response): array
    {
        return $response['response']['docs'];
    }
}
