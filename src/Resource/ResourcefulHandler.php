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

namespace Turahe\Core\Resource;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Turahe\Core\Contracts\Resources\ResourcefulRequestHandler;
use Turahe\Core\Contracts\Services\CreateService;
use Turahe\Core\Contracts\Services\DeleteService;
use Turahe\Core\Contracts\Services\UpdateService;
use Turahe\Core\Criteria\FilterRulesCriteria;
use Turahe\Core\Models\Model;
use Turahe\Core\Resource\Events\ResourceRecordCreated;
use Turahe\Core\Resource\Events\ResourceRecordUpdated;
use Turahe\Core\Resource\Http\ResourcefulRequest;

class ResourcefulHandler implements ResourcefulRequestHandler
{
    use AssociatesResources;

    /**
     * Initialize the resourceful handler.
     */
    public function __construct(protected ResourcefulRequest $request) {}

    /**
     * Handle the resource index action.
     */
    public function index(?Builder $query = null): LengthAwarePaginator
    {
        $query = $query ?: $this->resource()->newQuery();

        if ($criteria = $this->resource()->viewAuthorizedRecordsCriteria()) {
            $query->criteria($criteria);
        }

        // Allow passing filters to the index query or passing the filter ID
        $query->criteria($this->createFiltersCriteria());

        return $this->resource()->indexQuery(
            $this->resource()->order($query)
        )->criteria(
            $this->resource()->getRequestCriteria($this->request)
        )->paginate($this->getPerPage());
    }

    /**
     * Handle the resource store action.
     */
    public function store(): Model
    {
        $service = $this->resource()->service();

        if ($service instanceof CreateService) {
            $model = $service->create($this->request->all());
        } else {
            $model = $this->resource()::$model::create($this->request->all());
        }

        $model = $this->handleAssociatedResources($model);

        ResourceRecordCreated::dispatch($model, $this->resource());

        return $model;
    }

    /**
     * Handle the resource show action.
     */
    public function show(int $id, ?Builder $query = null): Model
    {
        $query = $query ?: $this->resource()->newQuery();

        return $this->resource()->displayQuery($query)->findOrFail($id);
    }

    /**
     * Handle the resource update action.
     */
    public function update(Model $model): Model
    {
        $service = $this->resource()->service();

        if ($service instanceof UpdateService) {
            $model = $service->update($model, $this->request->all());
        } else {
            $model->fill($this->request->all())->save();
        }

        $model = $this->handleAssociatedResources($model);

        ResourceRecordUpdated::dispatch($model, $this->resource());

        return $model;
    }

    /**
     * Handle the resource destroy action.
     */
    public function delete(Model $model): string
    {
        $service = $this->resource()->service();

        if ($service instanceof DeleteService) {
            $service->delete($model);
        } else {
            $model->delete();
        }

        return '';
    }

    /**
     * Force delete the resource record.
     */
    public function forceDelete(Model $model): string
    {
        $model->forceDelete();

        return '';
    }

    /**
     * Restore the soft deleted resource record.
     */
    public function restore(Model $model): void
    {
        $model->restore();
    }

    /**
     * Create filters criteria.
     */
    protected function createFiltersCriteria(): FilterRulesCriteria
    {
        return new FilterRulesCriteria(
            $this->request->get('filters', []),
            $this->resource()->filtersForResource($this->request),
            $this->request
        );
    }

    /**
     * Sync the given record associations.
     */
    protected function handleAssociatedResources(Model $record): Model
    {
        if ($this->resource()->isAssociateable()) {
            $associations = $this->filterAssociations(
                $this->resource(),
                $this->request->associateables()
            );

            $this->syncAssociations($this->resource(), $record->getKey(), $associations);
        }

        return $record;
    }

    /**
     * Get the resource from the request.
     */
    protected function resource(): Resource
    {
        return $this->request->resource();
    }

    /**
     * Get the number of models to return per page.
     */
    protected function getPerPage(): ?int
    {
        return $this->request->integer('per_page');
    }
}
