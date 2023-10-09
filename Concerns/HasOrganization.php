<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2023.
 *
 *
 */

namespace Modules\Core\Concerns;

use Laravel\Passport\HasApiTokens;

class HasOrganization
{
    /**
     * Determine if the given team is the current team.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function isCurrentOrganization($organization)
    {
        return $organization->id === $this->currentOrganization->id;
    }

    /**
     * Get the current team of the user's context.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentOrganization()
    {
        if (is_null($this->current_organization_id) && $this->id) {
            $this->switchOrganization($this->personalOrganization());
        }

        return $this->belongsTo(Jetstream::teamModel(), 'current_organization_id');
    }

    /**
     * Switch the user's context to the given team.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function switchOrganization($organization)
    {
        if (! $this->belongsToOrganization($organization)) {
            return false;
        }

        $this->forceFill([
            'current_organization_id' => $organization->id,
        ])->save();

        $this->setRelation('currentOrganization', $organization);

        return true;
    }

    /**
     * Get all of the teams the user owns or belongs to.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allOrganizations()
    {
        return $this->ownedOrganizations->merge($this->teams)->sortBy('name');
    }

    /**
     * Get all of the teams the user owns.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownedOrganizations()
    {
        return $this->hasMany(Jetstream::teamModel());
    }

    /**
     * Get all of the teams the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Jetstream::teamModel(), Jetstream::membershipModel())
            ->withPivot('role')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Get the user's "personal" team.
     *
     * @return \Modules\Users\Models\Organization
     */
    public function personalOrganization()
    {
        return $this->ownedOrganizations->where('personal_team', true)->first();
    }

    /**
     * Determine if the user owns the given team.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function ownsOrganization($organization)
    {
        if (is_null($organization)) {
            return false;
        }

        return $this->id == $organization->{$this->getForeignKey()};
    }

    /**
     * Determine if the user belongs to the given team.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function belongsToOrganization($organization)
    {
        if (is_null($organization)) {
            return false;
        }

        return $this->ownsOrganization($organization) || $this->teams->contains(function ($t) use ($organization) {
            return $t->id === $organization->id;
        });
    }

    /**
     * Get the role that the user has on the team.
     *
     * @param  mixed  $organization
     * @return \Laravel\Jetstream\Role|null
     */
    public function teamRole($organization)
    {
        if ($this->ownsOrganization($organization)) {
            return new OwnerRole;
        }

        if (! $this->belongsToOrganization($organization)) {
            return;
        }

        $role = $organization->users
            ->where('id', $this->id)
            ->first()
            ->membership
            ->role;

        return $role ? Jetstream::findRole($role) : null;
    }

    /**
     * Determine if the user has the given role on the given team.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function hasOrganizationRole($organization, string $role)
    {
        if ($this->ownsOrganization($organization)) {
            return true;
        }

        return $this->belongsToOrganization($organization) && optional(Jetstream::findRole($organization->users->where(
            'id',
            $this->id
        )->first()->membership->role))->key === $role;
    }

    /**
     * Get the user's permissions for the given team.
     *
     * @param  mixed  $organization
     * @return array
     */
    public function teamPermissions($organization)
    {
        if ($this->ownsOrganization($organization)) {
            return ['*'];
        }

        if (! $this->belongsToOrganization($organization)) {
            return [];
        }

        return (array) optional($this->teamRole($organization))->permissions;
    }

    /**
     * Determine if the user has the given permission on the given team.
     *
     * @param  mixed  $organization
     * @return bool
     */
    public function hasOrganizationPermission($organization, string $permission)
    {
        if ($this->ownsOrganization($organization)) {
            return true;
        }

        if (! $this->belongsToOrganization($organization)) {
            return false;
        }

        if (in_array(HasApiTokens::class, class_uses_recursive($this)) &&
            ! $this->tokenCan($permission) &&
            $this->currentAccessToken() !== null) {
            return false;
        }

        $permissions = $this->teamPermissions($organization);

        return in_array($permission, $permissions) ||
            in_array('*', $permissions) ||
            (Str::endsWith($permission, ':create') && in_array('*:create', $permissions)) ||
            (Str::endsWith($permission, ':update') && in_array('*:update', $permissions));
    }
}
