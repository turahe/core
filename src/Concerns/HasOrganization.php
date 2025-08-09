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
     * Cached organizations collection for performance optimization
     */
    private ?Collection $_organizationsCache = null;

    /**
     * Get all the organizations the user belongs to
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
     * Using PHP 8.4 auto-capture closures for cleaner syntax
     */
    public function scopeOfManager(Builder $query, User $user, bool $withCurrentUser = true): void
    {
        $query->where(function (Builder $query) use ($user, $withCurrentUser): void {
            $query->whereHas('organizations', fn (Builder $q) => $q->where('organizations.created_by', $user->getKey()))
                ->when($withCurrentUser, fn (Builder $q) => $q->orWhere('users.id', $user->getKey()));
        });
    }

    /**
     * Get all the organizations the user manages
     */
    public function managedOrganization(): HasMany
    {
        return $this->hasMany(Organization::class, 'created_by');
    }

    /**
     * Get all the organizations the user belongs to or manages
     * Using PHP 8.4 property hooks for caching
     */
    public function allOrganization(): Collection
    {
        return $this->_organizationsCache ??= $this->organizations
            ->merge($this->managedOrganization)
            ->sortBy('name');
    }

    /**
     * Clear the organizations cache when relationships change
     */
    public function clearOrganizationsCache(): void
    {
        $this->_organizationsCache = null;
    }

    /**
     * Determine if the user manages the given organization
     * Using match expression for cleaner logic
     */
    public function managesOrganization(Organization $organization): bool
    {
        return match (true) {
            $this->getKey() === $organization->created_by => true,
            default => false
        };
    }

    /**
     * Determine if the user belongs to the given organization
     * Enhanced with better performance using collection operations
     */
    public function belongsToTeam(Organization $organization): bool
    {
        if ($this->managesOrganization($organization)) {
            return true;
        }

        return $this->organizations->contains(
            fn (Organization $org) => $org->id === $organization->getKey()
        );
    }

    /**
     * Get organizations with specific role
     * Using PHP 8.4 array spread for better performance
     */
    public function organizationsWithRole(string ...$roles): Collection
    {
        return $this->organizations->filter(
            fn (Organization $org) => in_array($org->pivot->role, [...$roles], true)
        );
    }

    /**
     * Check if user has specific role in organization
     */
    public function hasRoleInOrganization(Organization $organization, string $role): bool
    {
        return $this->organizations
            ->where('id', $organization->getKey())
            ->first()?->pivot?->role === $role;
    }

    /**
     * Get count of managed organizations with type safety
     */
    public function getManagedOrganizationsCount(): int
    {
        return $this->managedOrganization()->count();
    }

    /**
     * Get count of member organizations with type safety
     */
    public function getMemberOrganizationsCount(): int
    {
        return $this->organizations()->count();
    }
}
