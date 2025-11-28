<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BaseRepositoryInterface
{
    /**
     * Get all
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Paginate all
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find model by the given ID
     *
     * @param int|string $modelId
     * @param array $columns
     * @return mixed
     */
    public function find(int|string $modelId, array $columns = ['*']): mixed;

    /**
     * Find model by a specific column
     *
     * @param string $field
     * @param mixed $value
     * @param array|null $with
     * @param array $columns
     * @return mixed
     */
    public function findBy(string $field, mixed $value, ?array $with = null, array $columns = ['*']): mixed;

    /**
     * Filter model
     *
     * @param array $filter ['attr' => 'value', 'attr_operator' => 'operator']
     * @param array|null $with ['relation']
     * @param array|null $withAggregate [['relation' => ['column' => '', 'function' => `fn`], ['relation' => '`function`'
     * @param array|null $orderBy ['column' => 'direction']
     * @param int $perPage
     * @param array|null $attrs
     * @param array $columns
     * @param array|null $whereHas
     * @param array|null $has
     * @param array|null $whereRelations [['name' => '', 'column' => '', 'operator' => '', 'value' => '']]
     * @param array|null $whereAdditional
     * @return LengthAwarePaginator
     */
    public function filter(
        array $filter,
        ?array $with = null,
        ?array $withAggregate = null,
        ?array $orderBy = null,
        int $perPage = -1,
        ?array $attrs = null,
        array $columns = ['*'],
        ?array $whereHas = null,
        ?array $has = null,
        ?array $whereRelations = null,
        ?array $whereAdditional = null
    ): LengthAwarePaginator;

    /**
     * Delete model
     *
     * @param int|string $modelId
     * @return bool|null
     */
    public function delete(int|string $modelId): ?bool;
}
