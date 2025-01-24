<?php

namespace App\Console\Commands;

use App\Factories\NewsProviderFactory;
use App\Repositories\Contracts\Article\ArticleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mockery\Exception;

class FetchArticleCommand extends Command
{
    /**
     * The name and signature of the console command.
     * Providers should separate with comma example: "newsapi,nyt".
     *
     * @var string
     */
    protected $signature = 'app:fetch-article {providers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch articles from selected providers.';

    public function __construct(public ArticleRepositoryInterface $articleRepository)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $providers = explode(',', $this->argument('providers'));
        foreach ($providers as $provider) {
            try {
                $fetcher = NewsProviderFactory::create($provider);

                $result = $fetcher->fetchNews(100, Carbon::now()->subHour()->startOfHour()->format('Y-m-d'),
                    Carbon::now()->startOfHour()->format('Y-m-d'));

                $this->articleRepository->insert($result);
            } catch (Exception $exception) {
                report($exception);
            }
        }
        return Command::SUCCESS;
    }
}
