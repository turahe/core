<?php

declare(strict_types=1);
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

namespace Turahe\Core\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Turahe\Core\Models\Organization;

trait HasOrganization
{
    /**
     * Get all the organizations the user belongs to
     */
    public function organizations(): MorphToMany
    {
        return $this->morphToMany(
            Organization::class,
            'model',
            'model_has_organization',
        )->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Scope a query to include only users that are managed by the given user.
     */
    public function scopeOfManager(Builder $query, User $user, $withCurrentUser = true): void
    {
        $query->where(function (Builder $query) use ($user, $withCurrentUser): void {
            $query->whereHas('organizations', fn (Builder $query) => $query->where('organizations.user_id', $user->getKey()))
                ->when($withCurrentUser, fn (Builder $query) => $query->orWhere('users.id', $user->getKey()));
        });
    }

    /**
     * Get all the organizations the user manages
     */
    public function managedOrganization(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    /**
     * Get all the organizations the user belongs to or manages
     */
    public function allOrganization(): Collection
    {
        return $this->organizations->merge($this->managedOrganization)->sortBy('name');
    }

    /**
     * Determine if the user manages the given team
     */
    public function managesOrganization(Organization $organization): bool
    {
        return $this->getKey() === $organization->{$this->getForeignKey()};
    }

    /**
     * Determine if the user belongs has the given team
     */
    public function belongsToTeam(Organization $organization): bool
    {
        return $this->managesOrganization($organization) || $this->organizations->contains(
            fn ($t) => $t->id === $organization->getKey()
        );
    }
}
