<?php

namespace App\Services\FetchArticle\Contracts;

interface NewsProviderInterface
{
    /**
     * Fetch news articles in a standardized format.
     *
     * @param int $limit
     * @param string|null $from (optional) Start date (ISO 8601 format).
     * @param string|null $to (optional) End date (ISO 8601 format).
     * @return array
     */
    public function fetchNews(int $limit, ?string $from = null, ?string $to = null): array;
}
