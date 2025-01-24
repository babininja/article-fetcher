<?php

namespace App\Services\FetchArticle\Models;

use App\Enums\Fetch\Provider;
use App\Services\FetchArticle\FetchArticleAbstract;
use Carbon\Carbon;

class GuardianNewsService extends FetchArticleAbstract
{
    protected string $apiKey;
    public string $method = 'get';

    public function __construct()
    {
        $this->apiKey = config('services.guardian.key');
        $this->path = config('services.guardian.base_url') . "/search";
    }

    /**
     * @param array $articles
     * @return array
     */
    public function mapToStandardFormat(array $articles): array
    {
        return array_map(function ($article) {
            return [
                'source' => 'The Guardian',
                'provider' => Provider::GUARDIAN,
                'author' => $article['fields']['byline'] ?? 'Unknown',
                'title' => $article['webTitle'],
                'content' => $article['fields']['body'] ?? '',
                'category' => $article['sectionName'],
                'published_at' => Carbon::parse($article['webPublicationDate']),
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
            'page-size' => $this->limit,
            'page' => $this->page,
            'api-key' => $this->apiKey,
        ];

        if ($from) {
            $params['from-date'] = $from;
        }
        if ($to) {
            $params['to-date'] = $to;
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
        return $response['response']['currentPage'] < $response['response']['pages'];
    }

    /**
     * @param array $response
     * @return array
     */
    public function getResult(array $response): array
    {
        return $response['response']['results'];
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [];
    }
}
