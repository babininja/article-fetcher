<?php

namespace App\Repositories\Models\Article;

use App\Models\Article\Article;
use App\Repositories\Contracts\Article\ArticleRepositoryInterface;
use App\Repositories\Models\BaseRepository;

class ArticleRepository extends BaseRepository implements ArticleRepositoryInterface
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * @return array
     */
    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    /**
     * @return string
     */
    public function model(): string
    {
        return Article::class;
    }
}
