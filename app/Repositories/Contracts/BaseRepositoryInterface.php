<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function makeModel(): Model;

    public function paginate(int $perPage, array $columns = ['*']): LengthAwarePaginator;

    public function advancePaginate(int $perPage, $filters = null, $sorts = null, $columns = null, $relations = []): LengthAwarePaginator;

    public function allQuery(array $search = [], $skip = null, $limit = null): Builder;

    public function all(array $search = [], $skip = null, $limit = null, array $columns = ['*']);

    public function create(array $input): Model;

    public function insert(array $input): void;

    public function find($id, $columns = ['*'], $relations = []);

    public function findOrFail($id, $columns = ['*']);

    public function update(array $input, $id);

    public function updateByModel(Model $model, array $input);

    public function delete($id);

    public function getTable(): string;
}
