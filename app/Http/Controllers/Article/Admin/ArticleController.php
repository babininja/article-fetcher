<?php

namespace App\Http\Controllers\Article\Admin;

use App\Enums\Setting\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Article\CreateArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\Article\ArticleAdminResource;
use App\Repositories\Contracts\Article\ArticleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class ArticleController extends Controller
{

    /**
     * @var ArticleRepositoryInterface
     */
    public ArticleRepositoryInterface $repo;

    /**
     * @param ArticleRepositoryInterface $repo
     */
    public function __construct(ArticleRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $filters = $request->get('filters', []);
        $columns = $request->get('columns', []);
        $sorts = $request->get('sorts', []);
        $list = $this->repo->advancePaginate($request->get('per_page', Pagination::DEFAULT_PER_PAGE->value),
            $filters, $sorts, $columns, []);

        return response()->successWithAdminPagination([
            'list' => ArticleAdminResource::collection($list),
            'paginate' => $list
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = $this->repo->find($id);
        if (!$item) {
            return response()->error(__('messages.response.NotFound'),'ERROR',Response::HTTP_NOT_FOUND);
        }

        return response()->success([
            'item' => ArticleAdminResource::make($item),
        ]);
    }
}
