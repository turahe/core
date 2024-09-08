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

namespace Turahe\Core\VisibilityGroup;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Turahe\Core\Models\ModelVisibilityGroup;
use Turahe\Users\Models\User;

trait RestrictsModelVisibility
{
    public static string $visibilityTypeOrganization = 'organization';

    public static string $visibilityTypeUsers = 'users';

    public static string $visibilityTypeAll = 'all';

    /**
     * Boot the trait.
     */
    protected static function bootRestrictsModelVisibility(): void
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->visibilityGroup?->delete();
            }
        });
    }

    /**
     * Get the mode visibility group.
     */
    public function visibilityGroup(): MorphOne
    {
        return $this->morphOne(ModelVisibilityGroup::class, 'visibilityable');
    }

    /**
     * Get the visibility group with it's dependencies
     */
    public function visibilityGroupData(): ?array
    {
        if (! $this->relationLoaded('visibilityGroup') || ! $this->visibilityGroup) {
            return null;
        }

        return [
            'type' => $this->visibilityGroup->type,
            'depends_on' => $this->getVisibilityDependentsIds(),
        ];
    }

    /**
     * Get organizations visiblity query
     */
    protected function getOrganizationVisibilityQuery(Builder $query, User $user): string
    {
        $raw = '';
        $query = clone $query;

        $query->whereHas('visibilityGroup.organizations', function ($q) use (&$raw, $user) {
            $raw = $this->getVisibilitySql(
                // Belongs to organization or manages organization
                $q->whereIn('dependable_id', $user->organizations->modelKeys())->orWhere('user_id', $user->getKey())
            );
        });

        return $raw;
    }

    /**
     * Get users visiblity query
     */
    protected function getUsersVisibilityQuery(Builder $query, User $user): string
    {
        $raw = '';
        $query = clone $query;

        $query->whereHas('visibilityGroup.users', function ($q) use (&$raw, $user) {
            $raw = $this->getVisibilitySql($q->whereIn('dependable_id', [$user->getKey()]));
        });

        return $raw;
    }

    /**
     * Check whether the model is visible to the given user
     */
    public function isVisible(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $this->visibilityGroup || $this->visibilityGroup->type === static::$visibilityTypeAll) {
            return true;
        }

        if ($this->visibilityGroup->type === static::$visibilityTypeOrganization) {
            foreach ($this->visibilityGroup->organizations as $organization) {
                if ($user->belongsToTeam($organization)) {
                    return true;
                }
            }
        }

        if ($this->visibilityGroup->type === static::$visibilityTypeUsers) {
            return in_array($user->getKey(), $this->visibilityGroup->users->modelKeys());
        }

        return false;
    }

    /**
     * Apply a scoped query to eager loader the visibility groups.
     */
    public function scopeWithVisibilityGroups(Builder $query): void
    {
        $query->with(['visibilityGroup.users', 'visibilityGroup.organizations']);
    }

    /**
     * Apply the scope to the given Eloquent query
     */
    public function scopeVisible(Builder $query, ?User $user = null): void
    {
        /** @var \Turahe\Users\Models\User */
        $user = $user ?: auth()->user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $query->whereHas('visibilityGroup', function ($q) use ($user, $query) {
            $q->whereRaw('CASE
                WHEN (type = "'.static::$visibilityTypeOrganization.'") THEN exists ('.$this->getOrganizationVisibilityQuery($query, $user).')
                WHEN (type = "'.static::$visibilityTypeUsers.'") THEN exists ('.$this->getUsersVisibilityQuery($query, $user).')
                ELSE 1=1
            END');
        })->orWhereDoesntHave('visibilityGroup');
    }

    /**
     * Get SQL for visibliity
     */
    protected function getVisibilitySql(Builder $query): string
    {
        $bindings = $query->getBindings();

        return preg_replace_callback('/\?/', function ($match) use (&$bindings, $query) {
            return $query->getConnection()->getPdo()->quote(array_shift($bindings));
        }, $query->toSql());
    }

    /**
     * Get the dependent ID's when the record has visibility group applied
     */
    protected function getVisibilityDependentsIds(): array
    {
        return collect([])->when(
            $this->shouldIncludeVisibilityGroup(static::$visibilityTypeOrganization),
            fn ($collection) => $collection->concat($this->visibilityGroup->organizations->modelKeys())
        )->when(
            $this->shouldIncludeVisibilityGroup(static::$visibilityTypeUsers),
            fn ($collection) => $collection->concat($this->visibilityGroup->users->modelKeys())
        )->all();
    }

    /**
     * Check whether a visibility group should be included in the response
     */
    protected function shouldIncludeVisibilityGroup(string $name): bool
    {
        return $this->visibilityGroup->type === $name && $this->visibilityGroup->relationLoaded($name);
    }

    /**
     * Save a visilbity group data the model
     */
    public function saveVisibilityGroup(array $visibility): static
    {
        $type = $visibility['type'] ?? static::$visibilityTypeAll;

        if (is_null($this->visibilityGroup)) {
            $this->visibilityGroup()->save(new ModelVisibilityGroup(['type' => $type]));
            $this->load('visibilityGroup');
        } else {
            $this->visibilityGroup->update(['type' => $type]);
            $this->visibilityGroup->{static::$visibilityTypeOrganization}()->detach();
            $this->visibilityGroup->{static::$visibilityTypeUsers}()->detach();
        }

        if ($type !== static::$visibilityTypeAll) {
            $this->visibilityGroup->{$type}()->attach($visibility['depends_on'] ?? []);
        }

        return $this;
    }
}
