<?php

namespace App\Providers;

use App\Services\Helper\HelperService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private array $models = [
        'Article\Article',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->makeMicros();
        $this->addServiceContainers();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }


    /**
     *  here we will use service container of laravel for dependency injection
     */
    private function addServiceContainers(): void
    {
        foreach ($this->models as $model) {
            $this->app->bind("App\Repositories\Contracts\\{$model}RepositoryInterface",
                "App\Repositories\Models\\{$model}Repository");
        }
    }


    /**
     * this function adds some custom functions to laravel`s request facade
     * to make a fixed response for all of apis
     */
    private function makeMicros(): void
    {
        Response::macro('error', function ($errors, string $message = 'ERROR', int $status = 400) {
            $response['result'] = $message;
            if (is_array($errors)) {
                if(isset($errors['message'])){
                    $response['message'] = $errors['message'];
                    $response['error'] = $errors['message'];
                }
                $response['errors'] = $errors;
            } else {
                $response['error'] = $errors;
            }

            return Response::make($response, $status);
        });

        Response::macro('success', function (array $data = [], string $message = 'SUCCESS', int $status = 200) {
            return Response::make([
                'data' => $data,
                'result' => $message,
            ], $status);
        });

        Response::macro('successJsonResource', function (JsonResource $data, string $message = 'SUCCESS', int $status = 200) {
            return Response::make([
                'data' => $data,
                'result' => $message,
            ], $status);
        });

        Response::macro('successWithPagination', function (array $data = [], string $message = 'SUCCESS', int $status = 200) {
            if (isset($data['paginate'])) {
                if ($data['paginate'] instanceof Paginator || $data['paginate'] instanceof LengthAwarePaginator) {
                    $helper = new HelperService();

                    $data['paginationLinks'] = [
                        'totalItems' => $data['paginate']->total(),
                        'perPage' => $data['paginate']->perPage(),
                        'nextPageUrl' => $helper->getPageNumber($data['paginate']->nextPageUrl()),
                        'previousPageUrl' => $helper->getPageNumber($data['paginate']->previousPageUrl()),
                        'lastPageUrl' => $helper->getPageNumber($data['paginate']->url($data['paginate']->lastPage())),
                    ];
                    unset($data['paginate']);
                }
            }

            return Response::make([
                'data' => $data,
                'result' => $message,
            ], $status);
        });


        Response::macro('successWithAdminPagination', function ($data = [], string $message = 'SUCCESS', int $status = 200) {
            if ($data instanceof Paginator || $data instanceof LengthAwarePaginator) {
                $data = ['list' => $data->items(), 'paginate' => $data];
            }
            if (isset($data['list']) && ($data['list'] instanceof Paginator || $data['list'] instanceof LengthAwarePaginator)) {
                $data = ['list' => $data['list']->items(), 'paginate' => $data['list']];
            }
            if (isset($data['paginate'])) {
                if ($data['paginate'] instanceof Paginator || $data['paginate'] instanceof LengthAwarePaginator) {
                    $helper = new HelperService();

                    $data['paginationLinks'] = [
                        'total' => $data['paginate']->total(),
                        'per_page' => $data['paginate']->perPage(),
                        'current_page' => $data['paginate']->currentPage(),
                        'last_page' => $helper->getPageNumber($data['paginate']->url($data['paginate']->lastPage())),
                    ];
                    unset($data['paginate']);
                }
            }

            return Response::make([
                'data' => $data,
                'result' => $message,
            ], $status);
        });
    }

}
