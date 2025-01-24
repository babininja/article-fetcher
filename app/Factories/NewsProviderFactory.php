<?php

namespace App\Factories;

use App\Enums\Fetch\Provider;
use App\Services\FetchArticle\Contracts\NewsProviderInterface;
use App\Services\FetchArticle\Models\GuardianNewsService;
use App\Services\FetchArticle\Models\NewsAPINewsService;
use App\Services\FetchArticle\Models\NYTNewsService;
use InvalidArgumentException;

class NewsProviderFactory
{
    /**
     * Get the news provider instance.
     *
     * @param string $provider
     * @return NewsProviderInterface
     * @throws InvalidArgumentException
     */
    public static function create(string $provider): NewsProviderInterface
    {
        return match (strtolower($provider)) {
            Provider::GUARDIAN->value => new GuardianNewsService(),
            Provider::NYT->value => new NYTNewsService(),
            Provider::NEWSAPI->value => new NewsAPINewsService(),
            default => throw new InvalidArgumentException("Unsupported provider: {$provider}"),
        };
    }
}
