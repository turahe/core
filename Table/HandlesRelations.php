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

namespace Modules\Core\Table;

trait HandlesRelations
{
    /**
     * Get all tables relation to be eager loaded with specific selected fields
     */
    protected function withRelationships(): array
    {
        $columns = $this->getEagerLoadableUserColumns();
        $selectWith = [];

        $with = $columns->reduce(
            fn (array $carry, RelationshipColumn $column) => $carry + $column->with,
            []
        );

        $columns->each(function ($column) use (&$with, &$selectWith) {
            $selectRelationFields = $this->getFieldsToSelectForRelationship($column);

            // Check if the relation is already queried
            // E.q. example usage on deals table
            // Column stage name and column stage win_probability from the same relation
            // In this case, Laravel will only perform query on the last selected relation
            // and the previous relation will be lost
            // for this reason we need to merge both relation in one select with the
            // selected fields from both relation

            $relationExists = collect($with)->contains(
                fn ($callback, $relationName) => $column->relationName === $relationName
            );

            if (! $relationExists) {
                $with[$column->relationName] = function ($query) use ($selectRelationFields) {
                    $query->select($selectRelationFields);
                };

                $selectWith[$column->relationName] = $selectRelationFields;
            } else {
                // Merge the selected relation fields
                $newSelect = array_unique(
                    array_merge($selectWith[$column->relationName], $selectRelationFields)
                );

                // Update the existent relation with the new merged select
                $with[$column->relationName] = function ($query) use ($newSelect) {
                    $query->select($newSelect);
                };
            }
        });

        return $with;
    }

    /**
     * Get the relations that should be counted
     */
    protected function countedRelationships(): array
    {
        return $this->getUserColumns()->reject(function ($column) {
            if (! $column->isCountable() || ($column->isCountable() && ! $column->counts())) {
                return true;
            }

            /**
             * Check if the table is sorting by countable has many column
             * If at the moment of the request the column is hidden and the user set
             * e.q. default sorting, an error will be triggered because the relationship
             * count is not queried, in this case
             *
             * In such case, we must query the column when is hidden to perform sorting
             */
            return $column->isHidden() && ! $this->isSortingByColumn($column);
        })
            ->map(fn ($column) => "{$column->relationName} as {$column->attribute}")
            ->all();
    }

    /**
     * Get the relationships to eager load.
     */
    protected function getEagerLoadableUserColumns()
    {
        return $this->getUserColumns()->reject(function ($column) {
            return ! $column->isRelation() ||
            $column->isHidden() ||
            ($column->isCountable() && $column->counts());
        });
    }

    /**
     * Get fields that should be selected with the eager loaded relation e.q. with(['company:id,name'])
     */
    protected function getFieldsToSelectForRelationship(RelationshipColumn $column): array
    {
        $select = [$this->getSelectableField($column)];

        // Adds the foreign key name to the select as is needed for Laravel to merge the data from the with query
        if ($column instanceof BelongsToColumn || $column instanceof MorphToManyColumn) {
            $select[] = $this->model->{$column->relationName}()->getRelated()->getQualifiedKeyName();
        } elseif ($column instanceof HasManyColumn || $column instanceof HasOneColumn) {
            $select[] = $this->model->{$column->relationName}()->getQualifiedForeignKeyName();
        } elseif ($column instanceof MorphManyColumn) {
            $select[] = $this->model->{$column->relationName}()->getModel()->getQualifiedKeyName();
            $select[] = $this->model->{$column->relationName}()->getQualifiedForeignKeyName();
        } elseif ($column instanceof BelongsToManyColumn) {
            $select[] = $this->model->{$column->relationName}()->getModel()->getQualifiedKeyName();
        }

        return collect($select)->merge(
            $this->qualifyColumn($column->select, $column->relationName)
        )->unique()->values()->all();
    }
}
