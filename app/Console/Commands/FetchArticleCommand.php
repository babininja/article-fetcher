<?php

namespace App\Console\Commands;

use App\Factories\NewsProviderFactory;
use App\Repositories\Contracts\Article\ArticleRepositoryInterface;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Command;

class FetchArticleCommand extends Command
{
    private $limit = 100;

    /**
     * The name and signature of the console command.
     * Providers should separate with comma example: "newsapi,nyt".
     *
     * @var string
     */
    protected $signature = 'app:fetch-article {providers : list of providers seperated by comma} {from? : start of period} {to? : end of period}';

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
        try {

            $providers = explode(',', $this->argument('providers'));
            $from = Carbon::parse($this->argument('from')) ?? Carbon::now()->subHour()->startOfHour();
            $to = Carbon::parse($this->argument('to')) ?? Carbon::now()->startOfHour();

            if ($from->gte($to)) {
                $this->info(__('messages.errors.invalid_date'));
                return Command::FAILURE;
            }

        } catch (InvalidFormatException $exception) {
            report($exception);
            $this->error(__('messages.errors.invalid_date'));
            return Command::FAILURE;
        }

        foreach ($providers as $provider) {
            try {
                $fetcher = NewsProviderFactory::create($provider);

                $result = $fetcher->fetchNews($this->limit, $from->format('Y-m-d'), $to->format('Y-m-d'));

                $this->articleRepository->insert($result);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
        return Command::SUCCESS;
    }
}
