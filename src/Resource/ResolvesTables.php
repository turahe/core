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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Turahe\Core\Fields\Field;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Table\Column;
use Turahe\Core\Table\DateTimeColumn;
use Turahe\Core\Table\ID;
use Turahe\Core\Table\Table;

/**
 * @mixin \Turahe\Core\Resource\Resource
 */
trait ResolvesTables
{
    /**
     * Resolve the resource table class
     */
    public function resolveTable(ResourceRequest $request): Table
    {
        $query = $this->newTableQuery();

        if ($criteria = $this->viewAuthorizedRecordsCriteria()) {
            $query->criteria($criteria);
        }

        $table = $this->table($query, $request)->setIdentifier($this->name());

        $this->tableColumns()
            ->when(
                $table->getColumns()->whereInstanceOf(ID::class)->isEmpty(),
                fn ($collection) => $collection->push($this->idField()->hidden())
            )
            ->reverse()
            ->each(fn (Column $column) => $table->getColumns()->prepend($column));

        // We will check if the tables has table wide actions and filters defined
        // If there are no table wide actions and filters, in this case, we will
        // set the table actions and filters directly from the resource defined.
        if ($table->resolveFilters($request)->isEmpty()) {
            $table->setFilters($this->filtersForResource($request));
        }

        if ($table->resolveActions($request)->isEmpty()) {
            $table->setActions($this->actionsForResource($request));
        }

        return $table;
    }

    /**
     * Resolve the resource trashed table class
     */
    public function resolveTrashedTable(ResourceRequest $request): Table
    {
        $query = $this->newTableQuery()->onlyTrashed();

        if ($criteria = $this->viewAuthorizedRecordsCriteria()) {
            $query->criteria($criteria);
        }

        $table = $this->table($query, $request)->setIdentifier($this->name().'-trashed');

        return $table->clearOrderBy()
            ->setColumns($this->trashedTableColumns($query))
            ->setActions($table->trashedViewActions())
            ->orderBy($query->getModel()->getDeletedAtColumn())
            ->customizeable(false); // Trashed tables are no customizeable
    }

    /**
     * Get the table columns from fields
     */
    public function tableColumns(): Collection
    {
        return $this->resolveFields()->filter->isApplicableForIndex()
            ->map(fn (Field $field) => $field->resolveIndexColumn())
            ->filter();
    }

    /**
     * Get the columns for trashed table.
     */
    protected function trashedTableColumns(Builder $query): Collection
    {
        return $this->tableColumns()
            ->prepend(DateTimeColumn::make(
                $query->getModel()->getDeletedAtColumn(),
                __('core::app.deleted_at')
            ))
            ->prepend($this->idField()->hidden())
            ->each(function (Column $column) {
                $column->hidden(false)->primary(false);
            });
    }

    /**
     * Get the table query.
     */
    public function newTableQuery(): Builder
    {
        return $this->newQuery();
    }
}
