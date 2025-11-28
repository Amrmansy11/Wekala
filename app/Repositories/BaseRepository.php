<?php

namespace App\Repositories;

use App\Contracts\Repositories\BaseRepositoryInterface;
use DateTime;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all
     *
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->query()->get($columns);
    }

    /**
     * Paginate all
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->query()->paginate($perPage, $columns);
    }

    /**
     * Find model by the given ID
     *
     * @param int|string $modelId
     * @param array $columns
     * @return mixed
     */
    public function find(int|string $modelId, array $columns = ['*']): mixed
    {
        return $this->model->query()->find($modelId, $columns);
    }

    /**
     * Find model by a specific column
     *
     * @param string $field
     * @param mixed $value
     * @param array|null $with
     * @param array $columns
     * @param array|null $whereRelations
     * @return mixed
     */
    public function findBy(
        string $field,
        mixed  $value,
        ?array $with = null,
        array  $columns = ['*'],
        ?array $whereRelations = null
    ): mixed
    {
        $query = $this->model->query()->where($field, $value);

        if ($with) {
            $query = $query->with($with);
        }
        if ($whereRelations) {
            foreach ($whereRelations as $whereRelation) {
                $query->whereRelation($whereRelation['name'], $whereRelation['column'], $whereRelation['value']);
            }
        }
        return $query->first($columns);
    }

    /**
     * Query Model
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->model->query();
    }

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
     * @throws Exception
     */
    public function filter(
        array  $filter,
        ?array $with = null,
        ?array $withAggregate = null,
        ?array $orderBy = null,
        int    $perPage = -1,
        ?array $attrs = null,
        array  $columns = ['*'],
        ?array $whereHas = null,
        ?array $has = null,
        ?array $whereRelations = null,
        ?array $whereAdditional = null
    ): LengthAwarePaginator
    {
        $query = $this->query();
        if ($with) {
            $query = $query->with($with);
        }
        if ($withAggregate) {
            foreach ($withAggregate as $relation => $withAg) {
                if (is_string($withAg)) {
                    $aggregateColumns = '*';
                    $aggregateFunction = $withAg;
                } else {
                    $aggregateColumns = $withAg['column'] ?? null;
                    $aggregateFunction = $withAg['function'] ?? null;
                }

                $query = $query->withAggregate($relation, $aggregateColumns, $aggregateFunction);
            }
        }
        $query = $this->filterQueryByFillable($query, $filter, $attrs);
        if ($whereAdditional) {
            $query = $this->filterQueryByFillable($query, $whereAdditional, $attrs);
        }

        $query = $this->filterQueryByDate($query, $filter, []);

        if ($orderBy) {
            $query = $this->filterOrderBy($query, $orderBy);
        }

        if ($whereHas) {
            foreach ($whereHas as $key => $value) {
                $query = $query->whereHas($key, $value);
            }
        }

        if ($has) {
            foreach ($has as $relation => $hasArr) {
                if (is_array($hasArr)) {
                    $query = $query->has($relation, $hasArr['operator'], $hasArr['value']);
                } else {
                    $query = $query->has($hasArr);
                }
            }
        }
        if ($whereRelations) {
            foreach ($whereRelations as $whereRelation) {
                $query = $query->whereRelation(
                    $whereRelation['name'],
                    $whereRelation['column'],
                    $whereRelation['operator'] ?? null,
                    $whereRelation['value'] ?? null
                );
            }
        }


        return $query->paginate($perPage, $columns);
    }

    /**
     * Store model
     *
     * @param array $data
     * @return Model
     */
    protected function store(array $data): Model
    {
        return $this->query()->create($data);
    }

    protected function findOrStore(array $data): Model
    {
        return $this->query()->firstOrCreate($data);
    }

    /**
     * Update model
     *
     * @param array $data
     * @param int|string $modelId
     * @return Model
     */
    protected function update(array $data, int|string $modelId): Model
    {
        $model = $this->find($modelId);
        $model->update($data);
        return $model;
    }

    /**
     * Delete model
     *
     * @param int|string $modelId
     * @return bool|null
     * @throws Exception
     */
    public function delete(int|string $modelId): ?bool
    {   
        return $this->find($modelId)->delete();
    }

    /**
     * Generate Query filter by syllables without dates
     *
     * @param Builder $query
     * @param array $filter filters key => value
     * @param array|null $attrs attrs to search by
     * @return Builder
     */
    public function filterQueryByFillable(Builder $query, array $filter, ?array $attrs = null): Builder
    {
        if (!$attrs) {
            $attrs = array_diff($this->model->getFillable(), $this->model->getDates());
        }

        // loop only filtered attributes
        foreach (array_intersect($attrs, array_keys($filter)) as $key) {
            // check if key exist in filter
            if (isset($filter[$key])) {
                if (is_array($filter[$key])) {
                    $query = $query->whereIn($key, $filter[$key]);
                } else {
                    // get operator
                    $operator = $filter["{$key}_operator"] ?? '=';
                    $value = $filter[$key];
                    // handle the like operator
                    if ($operator === 'like') {
                        $value = "%$value%";
                    }

                    if ($operator === 'null') {
                        $query = $query->whereNull($key);
                    } elseif ($operator === 'not_null') {
                        $query = $query->whereNotNull($key);
                    } else {
                        $query = $query->where($key, $operator, $value);
                    }
                }
            }
        }

        return $query;
    }

    /**
     * Generate Query filter by dates
     *
     * @param Builder $query
     * @param array $filter
     * @param array|null $attrs
     * @return Builder
     * @throws Exception
     */
    public function filterQueryByDate(Builder $query, array $filter, ?array $attrs = null): Builder
    {
        if (!$attrs) {
            $attrs = $this->model->getDates();
        }
        foreach ($attrs as $key) {
            // check if key exist in filter
            if ($filter[$key] ?? false) {
                $query = $query->where($key, '>=', new DateTime($filter[$key]))
                    ->where('created_at', '<=', (new DateTime($filter[$key]))->modify('+1 day'));
            } else {
                // from
                if ($filter["{$key}_from"] ?? false) {
                    $query = $query->where($key, '>=', new DateTime($filter["{$key}_from"]));
                }

                // to
                if ($filter["{$key}_to"] ?? false) {
                    $query = $query->where('created_at', '<=', (new DateTime($filter["{$key}_to"]))->modify('+1 day'));
                }
            }
        }

        return $query;
    }

    /**
     * Generate Query sorted
     *
     * @param Builder $query
     * @param array $orderBy
     * @return Builder
     */
    public function filterOrderBy(Builder $query, array $orderBy): Builder
    {
        foreach ($orderBy as $column => $direction) {
            return $query->orderBy($column, $direction);
        }
        return $query;
    }

    /**
     * Sets relations for eager loading.
     *
     * @param array $relations
     * @return Builder
     */
    public function with(array $relations): Builder
    {
        return $this->query()->with($relations);
    }
}
