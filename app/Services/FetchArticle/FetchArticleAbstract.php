<?php

namespace App\Services\FetchArticle;

use App\Services\FetchArticle\Contracts\NewsProviderInterface;
use Illuminate\Support\Facades\Http;

abstract class FetchArticleAbstract implements NewsProviderInterface
{
    public string $path;
    public string $method;

    public int $page;
    public int $limit;

    /**
     * @param string|null $from
     * @param string|null $to
     * @return array
     */
    public abstract function getParams(?string $from = null, ?string $to = null): array;

    /**
     * @return int
     */
    public abstract function getFirstPage(): int;

    /**
     * @return array
     */
    public abstract function getHeaders(): array;

    /**
     * @param array $response
     * @return bool
     */
    public abstract function hasMorePage(array $response): bool;

    /**
     * @param array $response
     * @return array
     */
    public abstract function getResult(array $response): array;

    /**
     * @param array $articles
     * @return array
     */
    public abstract function mapToStandardFormat(array $articles): array;

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }


    /**
     * @param int $limit
     * @param string|null $from
     * @param string|null $to
     * @return array
     * @throws \Exception
     */
    public function fetchNews(int $limit, ?string $from = null, ?string $to = null): array
    {
        $articles = [];
        $this->page = $this->getFirstPage();
        $this->limit = $limit;

        do {
            $params = $this->getParams($from, $to);
            $method = $this->getMethod();
            $path = $this->getPath();

            $response = Http::withHeaders($this->getHeaders())->$method($path, $params);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch articles: ' . $response->body());
            }

            $jsonResponse = $response->json();
            $docs = $this->getResult($jsonResponse);
            $articles = array_merge($articles, $this->mapToStandardFormat($docs));

            $this->page++;
            $hasMorePages = $this->hasMorePage($jsonResponse);
        } while ($hasMorePages);

        return $articles;
    }
}
