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

namespace Modules\Core\Resource;

use Modules\Core\Models\Model;
use Modules\Core\Fields\HasMany;
use Modules\Core\Fields\MorphMany;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Concerns\UserOrderable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Models\PinnedTimelineSubject;
use Modules\Core\Contracts\Fields\Customfieldable;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @mixin \Modules\Core\Resource\Resource
 */
trait QueriesResources
{
    /**
     * Get a new query builder for the resource's model table.
     */
    public static function newQuery(): Builder
    {
        return static::newModel()->newQuery();
    }

    /**
     * Prepare display query.
     */
    public function displayQuery(?Builder $query = null): Builder
    {
        $query ??= static::newQuery();

        [$with, $withCount] = static::getEagerLoadable($this->resolveFields());

        return $query->withCommon()
            ->withCount($withCount->all())
            ->with($with->all());
    }

    /**
     * Prepare index query.
     */
    public function indexQuery(?Builder $query = null): Builder
    {
        $query ??= static::newQuery();

        [$with, $withCount] = static::getEagerLoadable($this->fieldsForIndexQuery());

        return $query->withCount($withCount->all())->with($with->all());
    }

    /**
     * Prepare resource global search query.
     */
    public function globalSearchQuery(?Builder $query = null): Builder
    {
        return $query ??= static::newQuery();
    }

    /**
     * Prepare resource search query.
     */
    public function searchQuery(?Builder $query = null): Builder
    {
        $query ??= static::newQuery();

        [$with, $withCount] = static::getEagerLoadable($this->resolveFields());

        return $query->withCount($withCount->all())->with($with->all());
    }

    /**
     * Prepare resource search query when searching trashed records.
     */
    public function searchTrashedQuery(?Builder $query = null): Builder
    {
        $query ??= static::newQuery();

        [$with, $withCount] = static::getEagerLoadable($this->resolveFields());

        return $query->withCount($withCount->all())->with($with->all());
    }

    /**
     * Create the query when the resource is associated and the data is intended for the timeline.
     */
    public function timelineQuery(Model $subject): Builder
    {
        $query = $this->associatedIndexQuery($subject, false)
            ->with('pinnedTimelineSubjects')
            ->withPinnedTimelineSubjects($subject)
            ->orderBy((new PinnedTimelineSubject)->getQualifiedCreatedAtColumn(), 'desc');

        if ($query->getModel()->usesTimestamps()) {
            $query->orderBy($query->getModel()->getQualifiedCreatedAtColumn(), 'desc');
        }

        return $query;
    }

    /**
     * Create query when the resource is associated for index.
     */
    public function associatedIndexQuery(Model $primary, bool $latest = true): Builder
    {
        $model = static::newModel();
        $relation = Innoclapps::resourceByModel($primary)->associateableName();

        return static::newQuery()->select(static::prefixColumns())
            ->with($this->withWhenAssociated())
            ->withCount($this->withCountWhenAssociated())
            ->whereHas($relation, function ($query) use ($primary) {
                return $query->where($primary->getKeyName(), $primary->getKey());
            })->when($latest && $model->usesTimestamps(), function ($instance) use ($model) {
                $instance->orderBy($model->getQualifiedCreatedAtColumn(), 'desc');
            });
    }

    /**
     * Apply the order from the resource to the given query.
     */
    public function order(Builder $query): Builder
    {
        if (in_array(UserOrderable::class, class_uses_recursive(static::$model))) {
            return $query->userOrdered();
        }

        return $query->orderBy(static::$orderBy, static::$orderByDir);
    }

    /**
     * Get the relations to eager load when quering associated records
     */
    public function withWhenAssociated(): array
    {
        return [];
    }

    /**
     * Get the countable relations when quering associated records
     */
    public function withCountWhenAssociated(): array
    {
        return [];
    }

    /**
     * Get the eager loadable relations from the given fields
     */
    public static function getEagerLoadable($fields): array
    {
        $with = $fields->pluck('belongsToRelation');

        $hasMany = $fields->whereInstanceOf(HasMany::class)->reject(function ($field) {
            return $field->excludeFromZapierResponse && request()->isZapier();
        });

        $morphMany = $fields->whereInstanceOf(MorphMany::class)->reject(function ($field) {
            return $field->excludeFromZapierResponse && request()->isZapier();
        });

        $customFieldAble = $fields->whereInstanceOf(Customfieldable::class);

        $fieldCustomProvided = $fields->filter(function ($field) {
            return method_exists($field, 'withRelationships');
        })->map(fn ($field) => $field->withRelationships());

        $with = $with->merge($hasMany->filter(function ($field) {
            return $field->count === false;
        })
            ->pluck('hasManyRelationship'))->merge($morphMany->filter(function ($field) {
                return $field->count === false;
            })->pluck('morphManyRelationship'))
            ->merge($customFieldAble->filter(function ($field) {
                return $field->isCustomField() && $field->isOptionable();
            })->pluck('customField.relationName'));

        if ($fieldCustomProvided->isNotEmpty()) {
            $with = $with->merge(...$fieldCustomProvided->all());
        }

        $withCount = $hasMany->push(...$morphMany->all())->filter(function ($field) {
            return $field->count === true;
        })->map(function ($field) {
            $relationName = $field instanceof HasMany ? 'hasManyRelationship' : 'morphManyRelationship';

            return $field->{$relationName}.' as '.$field->countKey();
        });

        return array_map(function ($collection) {
            return $collection->filter()->unique();
        }, [$with, $withCount]);
    }

    /**
     * Get the fields when creating index query
     *
     * @return \Illuminate\Support\Collection
     */
    protected function fieldsForIndexQuery()
    {
        return $this->resolveFields()->reject(function ($field) {
            return $field instanceof HasMany;
        });
    }

    /**
     * Prefix the database table columns for the given resource
     *
     * @param  \Illuminate\Database\Eloquent\Model|string|null  $model
     */
    public static function prefixColumns($model = null): array
    {
        if ($model instanceof EloquentModel) {
            $table = $model->getTable();
        } elseif (is_string($model)) {
            $table = $model;
        } else {
            $table = Innoclapps::resourceByName(static::name())->newModel()->getTable();
        }

        return collect(\Schema::getColumnListing($table))
            ->map(function ($column) use ($table) {
                return $table.'.'.$column;
            })->all();
    }
}
