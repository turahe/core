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

/**
 * HasOrganization Trait
 * 
 * Provides organization management functionality for Eloquent models.
 * This trait allows models (typically User models) to belong to multiple organizations
 * and manage their own organizations with role-based access control.
 * 
 * Features:
 * - Many-to-many relationship with organizations
 * - Role-based organization membership
 * - Organization management capabilities
 * - Query scoping for organization-based filtering
 * 
 * @package Turahe\Core\Concerns
 */
trait HasOrganization
{
    /**
     * Get all the organizations the user belongs to
     * 
     * Returns a many-to-many relationship with organizations through a pivot table.
     * The relationship includes role information and timestamps for audit purposes.
     * 
     * @return MorphToMany Relationship to organizations with role and timestamp data
     */
    public function organizations(): MorphToMany
    {
        return $this->morphToMany(
            Organization::class,
            'model',
            config('core.tables.model_has_organization'),
        )->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Scope a query to include only users that are managed by the given user.
     * 
     * This scope filters users based on whether they belong to organizations
     * created by the specified manager user. Optionally includes the manager user
     * themselves in the results.
     * 
     * @param Builder $query The query builder instance
     * @param User $user The manager user to filter by
     * @param bool $withCurrentUser Whether to include the manager user in results
     */
    public function scopeOfManager(Builder $query, User $user, $withCurrentUser = true): void
    {
        $query->where(function (Builder $query) use ($user, $withCurrentUser): void {
            $query->whereHas('organizations', fn (Builder $query) => $query->where('organizations.created_by', $user->getKey()))
                ->when($withCurrentUser, fn (Builder $query) => $query->orWhere('users.id', $user->getKey()));
        });
    }

    /**
     * Get all the organizations the user manages
     * 
     * Returns a one-to-many relationship with organizations that the user
     * has created and therefore manages.
     * 
     * @return HasMany Relationship to managed organizations
     */
    public function managedOrganization(): HasMany
    {
        return $this->hasMany(Organization::class, 'created_by');
    }

    /**
     * Get all the organizations the user belongs to or manages
     * 
     * Combines organizations the user belongs to and organizations they manage,
     * then sorts the result by organization name for consistent ordering.
     * 
     * @return Collection Combined and sorted collection of all related organizations
     */
    public function allOrganization(): Collection
    {
        return $this->organizations->merge($this->managedOrganization)->sortBy('name');
    }

    /**
     * Determine if the user manages the given organization
     * 
     * Checks if the user is the creator of the specified organization,
     * which grants them management privileges.
     * 
     * @param Organization $organization The organization to check
     * @return bool True if the user manages the organization
     */
    public function managesOrganization(Organization $organization): bool
    {
        return $this->getKey() === $organization->created_by;
    }

    /**
     * Determine if the user belongs to the given organization
     * 
     * Checks if the user either manages the organization or is a member of it.
     * This method provides a comprehensive way to check organization membership.
     * 
     * @param Organization $organization The organization to check
     * @return bool True if the user belongs to or manages the organization
     */
    public function belongsToTeam(Organization $organization): bool
    {
        return $this->managesOrganization($organization) || $this->organizations->contains(
            fn ($t) => $t->id === $organization->getKey()
        );
    }
}
