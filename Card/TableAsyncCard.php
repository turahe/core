<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

namespace Modules\Core\Card;

use Modules\Core\JsonResource;
use Illuminate\Support\Facades\Request;
use Modules\Core\Contracts\Presentable;
use Illuminate\Database\Query\Expression;
use Modules\Core\Criteria\RequestCriteria;
use Modules\Core\ProvidesModelAuthorizations;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class TableAsyncCard extends Card
{
    use ProvidesModelAuthorizations;

    /**
     * Default sort field.
     */
    protected Expression|string|null $sortBy = 'id';

    /**
     * Default sort direction.
     */
    protected string $sortDirection = 'asc';

    /**
     * Default per page.
     */
    protected int $perPage = 15;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected ?Builder $query = null;

    /**
     * Provide the query that will be used to retrieve the items.
     */
    abstract public function query(): Builder;

    /**
     * Get the query instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQuery()
    {
        return $this->query ??= $this->query()->criteria(RequestCriteria::class);
    }

    /**
     * Retrieve the items from storage.
     */
    protected function performQuery(): LengthAwarePaginator
    {
        $query = $this->getQuery();

        if ($sortBy = $this->getSortColumn()) {
            $query->orderBy($sortBy, $this->sortDirection);
        }

        return tap($query->paginate(
            $this->getPerPage(),
            $this->selectColumns()
        ), function ($data) {
            $this->query = null;
        });
    }

    /**
     * Get the sort column.
     */
    protected function getSortColumn(): Expression|string|null
    {
        return $this->sortBy;
    }

    /**
     * Get the number of models to return per page.
     */
    protected function getPerPage(): int
    {
        return Request::integer('per_page', $this->perPage);
    }

    /**
     * Provide the table fields.
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Get the columns that should be selected in the query.
     */
    protected function selectColumns(): array
    {
        return collect($this->fields())->reject(function ($field) {
            return isset($field['select']) && $field['select'] === false;
        })->pluck('key')->push(
            $this->getQuery()->getModel()->getKeyName()
        )->all();
    }

    /**
     * Parse the query result.
     */
    protected function transformResult(LengthAwarePaginator $result): LengthAwarePaginator
    {
        $result->getCollection()->transform(fn ($model) => $this->mapRow($model));

        return $result;
    }

    /**
     * Map the given model into a row.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array
     */
    protected function mapRow($model)
    {
        $result = collect($this->fields())
            ->merge(array_map(fn ($column) => ['key' => $column], $this->selectColumns()))
            ->unique('key')
            ->mapWithKeys(function (array $field) use ($model) {
                $value = isset($field['format']) ? $field['format']($model) : data_get($model, $field['key']);

                return [$field['key'] => $value];
            })->all();

        if ($model instanceof Presentable) {
            $result['path'] = $model->path;
        }

        $result['authorizations'] = $this->getAuthorizations($model);

        return $result;
    }

    /**
     * Define the card component used on front end.
     */
    public function component(): string
    {
        return 'card-table-async';
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'fields' => $this->fields(),
            'items'  => JsonResource::collection($this->transformResult($this->performQuery()))
                ->toResponse(Request::instance())
                ->getData(),
        ]);
    }
}
