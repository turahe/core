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

namespace Modules\Core\Contracts\Resources;

use Modules\Core\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResourcefulRequestHandler
{
    /**
     * Get the results for the index action from the given query.
     */
    public function index(?Builder $query = null): LengthAwarePaginator|Paginator|Collection|iterable;

    /**
     * Store new resource record in storage.
     */
    public function store(): Model;

    /**
     * Get record model from the given ID and query.
     */
    public function show(int $id, ?Builder $query = null): Model;

    /**
     * Perform update on the given model.
     */
    public function update(Model $id): Model;

    /**
     * Perform delete on the given model.
     */
    public function delete(Model $model): mixed;

    /**
     * Perform force delete on the given model.
     */
    public function forceDelete(Model $model): mixed;

    /**
     * Perform restore on the given model.
     */
    public function restore(Model $model): void;
}
