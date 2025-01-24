<?php

namespace App\Repositories\Models;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as Application;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Builder|Builder[]|Collection|Model|null
     */
    protected array|null|Builder|Collection|Model $model;

    /**
     * @var Application
     */
    protected Application $app;


    /**
     * @var string
     */
    protected string $default_operator = '=';

    /**
     * @var string
     */
    protected string $default_sort_field = 'id';

    /**
     * @var string
     */
    protected string $default_dir = 'desc';

    /**
     * @param Application $app
     *
     * @throws Exception
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Get searchable fields array
     *
     * @return array
     */
    abstract public function getFieldsSearchable(): array;

    /**
     * Configure the Models
     *
     */
    abstract public function model();

    /**
     * Make Models instance
     *
     * @return Model
     * @throws Exception
     *
     */
    public function makeModel(): Model
    {
        $model = $this->app->make($this->model());
        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Models");
        }

        return $this->model = $model;
    }

    /**
     * Paginate records for scaffold.
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage, array $columns = ['*']): LengthAwarePaginator
    {
        $query = $this->allQuery();
        if (empty($columns)) {
            $columns = ['*'];
        }
        return $query->paginate($perPage, $columns);
    }


    /**
     * Paginate records for scaffold.
     *
     * @param int $perPage
     * @param mixed $filters
     * @param mixed $sorts
     * @param mixed $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function advancePaginate(int $perPage, $filters = null, $sorts = null, $columns = null, $relations = []): LengthAwarePaginator
    {
        return $this->getAdvancePaginateResult($perPage, $filters, $sorts, $columns, $relations);
    }

    /**
     * @param $modelQuery
     * @param int $perPage
     * @param $filters
     * @param $sorts
     * @param $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function advancePaginateByModel($modelQuery, int $perPage, $filters = null, $sorts = null, $columns = null, array $relations = []): LengthAwarePaginator
    {
        return $this->getAdvancePaginateResult($perPage, $filters, $sorts, $columns, $relations, $modelQuery);
    }

    /**
     * @param int $perPage
     * @param $filters
     * @param $sorts
     * @param $columns
     * @param array $relations
     * @param $modelQuery
     * @return LengthAwarePaginator
     */
    private function getAdvancePaginateResult(int $perPage, $filters = null, $sorts = null, $columns = null, array $relations = [], $modelQuery = null): LengthAwarePaginator
    {
        $filters = is_array($filters) ? $filters : [];
        $filters = array_slice($filters, 0, config('app.pagination_filters_max'));
        $query = $this->allQuery($filters, null, null, $modelQuery);
        if (count($relations) > 0) {
            $query = $query->with($relations);
        }
        if (!empty($columns)) {
            if (!is_array($columns)) {
                $columns = explode(',', $columns);
            }
            $columns = array_slice($columns, 0, config('app.pagination_columns_max'));
            $columns = array_intersect($this->getAllTableFields(), $columns);
        }
        if (empty($columns)) {
            $columns = ['*'];
        }
        if (!empty($sorts) && is_array($sorts)) {
            $sorts = array_slice($sorts, 0, config('app.pagination_sorts_max'));
            foreach ($sorts as $sort) {
                if (isset($sort['name']) && in_array($sort['name'], $this->getAllTableFields())) {
                    $sort['direction'] = $sort['direction'] ?? $this->default_dir;
                    $query->orderBy($sort['name'], strtoupper($sort['direction']));
                }
            }
        } else {
            $query->orderBy($this->default_sort_field, $this->default_dir);
        }
        return $query->paginate($perPage, $columns);
    }

    /**
     * @param array $search
     * @param $skip
     * @param $limit
     * @param $query
     * @return Builder
     */
    public function allQuery(array $search = [], $skip = null, $limit = null, $query = null): Builder
    {
        if (!($query instanceof Builder)) {
            $query = $this->newQuery();
        }
        if (count($search)) {
            $query = $this->applySearch($query, $search);
        }

        if (!is_null($skip)) {
            $query->skip($skip);
        }

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Retrieve all records with given filter criteria
     *
     * @param array $search
     * @param int|null $skip
     * @param int|null $limit
     * @param array $columns
     *
     * @return Builder[]|Collection
     */
    public function all(array $search = [], $skip = null, $limit = null, array $columns = ['*']): Collection|array
    {
        $query = $this->allQuery($search, $skip, $limit);

        return $query->get($columns);
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create(array $input): Model
    {
        $model = $this->model->newInstance($input);

        $model->save();

        return $model;
    }


    /**
     * Create model record
     *
     * @param array $input
     *
     * @return void
     */
    public function insert(array $input): void
    {
        $this->model->insert($input);
    }

    /**
     * Find model record for given id
     *
     * @param int $id
     * @param array $columns
     *
     * @param array $relations
     * @param array $countRelations
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function find($id, $columns = ['*'], $relations = [], array $countRelations = []): Model|Collection|Builder|array|null
    {
        $query = $this->newQuery();
        if (count($relations) > 0) {
            $query = $query->with($relations);
        }

        if (!empty($countRelations)) {
            $query = $query->withCount($countRelations);
        }

        return $query->find($id, $columns);
    }

    /**
     * Find model record for given id Or fail
     *
     * @param int $id
     * @param array $columns
     *
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function findOrFail($id, $columns = ['*']): Model|Collection|Builder|array|null
    {
        $query = $this->newQuery();

        return $query->findOrFail($id, $columns);
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int $id
     *
     * @return Builder|Builder[]|Collection|Model|boolean
     */
    public function update(array $input, $id): Model|Collection|Builder|bool|array
    {
        try {
            $query = $this->newQuery();

            $model = $query->findOrFail($id);

            $model->fill($input);

            $model->save();

            return $model;
        } catch (Exception $e) {
            report($e);
        }
        return false;
    }

    /**
     * Update model record for given model
     *
     * @param Model $model
     * @param array $input
     * @return Builder|Builder[]|Collection|Model|boolean
     */
    public function updateByModel(Model $model, array $input): Model|Collection|Builder|bool|array
    {
        try {
            $model->fill($input);
            $model->save();
            return $model;
        } catch (Exception $e) {
            report($e);
        }
        return false;
    }

    /**
     * @param int $id
     *
     * @return bool|mixed|null
     * @throws Exception
     *
     */
    public function delete($id): mixed
    {
        $query = $this->newQuery();

        $model = $query->findOrFail($id);

        return $model->delete();
    }


    /**
     * @param $query
     * @param array $search
     * @return mixed
     */
    protected function applySearch($query, array $search): mixed
    {
        foreach ($search as $key => $filter) {
            if (is_array($filter) && isset($filter['name'])) {
                $filter['value'] = $filter['value'] ?? '';
                $filter['operator'] = $filter['operator'] ?? ($filter['operation'] ?? $this->default_operator);
                $this->applySearchFilter($query, $filter['name'], $filter['value'], $filter['operator']);
            } else {
                $this->applySearchFilter($query, $key, $filter, $this->default_operator);
            }
        }
        return $query;
    }

    /**
     * @param $query
     * @param $name
     * @param $value
     * @param $operator
     * @return $this
     */
    protected function applySearchFilter(&$query, $name, $value, $operator): static
    {
        $searchableFields = $this->getAllowedSearchableFields($this->getFieldsSearchable());
        if (in_array($name, $searchableFields)) {
            $this->applyFilterOperator($query, $name, $value, $operator);
        }
        return $this;
    }


    /**
     * @param $query
     * @param $name
     * @param $value
     * @param $operator
     * @return void
     */
    protected function applyFilterOperator(&$query, $name, $value, $operator = null): void
    {
        $operator = empty($operator) ? $this->default_operator : $operator;
        switch ($operator) {
            case 'like':
                $query->where($name, 'like', '%' . $value . '%');
                break;
            case 'in':
                $query->whereIn($name, $this->getValueCorrectFormat($value));
                break;
            case 'notIn':
                $query->whereNotIn($name, $this->getValueCorrectFormat($value));
                break;
            case 'between':
                $query->whereBetween($name, $this->getValueCorrectFormat($value));
                break;
            case 'notBetween':
                $query->whereNotBetween($name, $this->getValueCorrectFormat($value));
                break;
            case 'isNull':
                $query->whereNull($name);
                break;
            case 'notNull':
                $query->whereNotNull($name);
                break;
            case in_array($operator, ['=', '>', '<', '>=', '<=', '<>', '!=']):
                $query->where($name, $operator, $value);
                break;
            default:
                abort(422, 'Invalid operator');
        }
    }

    private function getValueCorrectFormat($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        return explode(',', $value);
    }

    /**
     * @param $columns
     * @return array
     */
    private function getAllowedSearchableFields($columns): array
    {
        if (empty($columns) || in_array('*', $columns)) {
            return $this->getAllTableFields();
        }
        return $columns;
    }

    /**
     * @return array
     */
    private function getAllTableFields(): array
    {
        $table_name = $this->getTable();
        $cache_key = "table_" . $table_name . "_columns";
        return Cache::rememberForever($cache_key, function () use ($table_name) {
            return Schema::getColumnListing($table_name);
        });
    }

    /**
     * @return Builder
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }


    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->model->getTable();
    }

    /**
     * @param $filters
     * @param $name
     * @return array
     */
    protected function getFindFilterInfo($filters, $name): array
    {
        if (is_array($filters)) {
            foreach ($filters as $key => $filter) {
                if (isset($filter['name']) && $filter['name'] == $name && !empty($filter['value'])) {
                    $filter['operator'] = $filter['operator'] ?? ($filter['operation'] ?? $this->default_operator);
                    return [true, $filter, $key];
                }
            }
        }
        return [false, false, 0];
    }
}
