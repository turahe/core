<?php

declare(strict_types=1);
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

namespace Turahe\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;
use Turahe\Core\Concerns\HasSettings;
use Turahe\Core\Concerns\HasTaxonomies;
use Turahe\Core\Enums\OrganizationType;
use Turahe\UserStamps\Concerns\HasUserStamps;

/**
 * Organization Model
 *
 * Represents an organization entity with hierarchical structure, settings,
 * taxonomies, and user management capabilities.
 *
 * Features:
 * - Hierarchical organization structure using nested sets
 * - Configurable primary key (ULID, UUID, or auto-increment)
 * - Flexible settings management
 * - Taxonomy categorization
 * - Slug generation for SEO-friendly URLs
 * - Soft deletes and pruning
 * - User stamps for audit trails
 * - Sortable ordering
 */
class Organization extends Model implements Sortable
{
    use HasConfigurablePrimaryKey;
    use HasSettings;
    use HasSlug;
    use HasTaxonomies;
    use HasUserStamps;
    use NodeTrait;
    use Prunable;
    use SoftDeletes;
    use SortableTrait;

    protected $fillable = [
        'name',
        'code',
        'type',
        'slug',
        'parent_id',
    ];

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('core.tables.organizations');
    }

    /**
     * Get the left boundary column name for nested sets
     *
     * @return string The left boundary column name
     */
    public function getLftName(): string
    {
        return 'record_left';
    }

    /**
     * Get the right boundary column name for nested sets
     *
     * @return string The right boundary column name
     */
    public function getRgtName(): string
    {
        return 'record_right';
    }

    /**
     * Get the parent ID column name for nested sets
     *
     * @return string The parent ID column name
     */
    public function getParentIdName(): string
    {
        return 'parent_id';
    }

    /**
     * Set the parent organization for nested set hierarchy
     *
     * This method allows setting the parent organization using the parent
     * attribute, which internally calls setParentIdAttribute for proper
     * nested set management.
     *
     * @param  mixed  $value  The parent organization or ID
     *
     * @throws \Exception When parent setting fails
     */
    public function setParentAttribute($value): void
    {
        $this->setParentIdAttribute($value);
    }

    public $sortable = [
        'order_column_name' => 'record_ordering',
        'sort_when_creating' => true,
    ];

    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
        ];
    }

    /**
     * @var string[]
     */
    protected $defaultSettings = [
        'language',
        'timezone',
    ];

    // settings rules
    /**
     * @var array|string[]
     */
    public array $settingsRules = [
        'datetime' => 'string',
        'language' => 'string|exists:tm_languages,code',
        'timezone' => 'timezone:all',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Get all the users that belong to the team
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            config('auth.providers.users.model'),
            'model',
            config('core.tables.model_has_organization'),
            'model_id',
            'organization_id',
        )->withPivot('role');
    }

    /**
     * Get all the team's users including its manager
     */
    public function allUsers(): Collection
    {
        return $this->users->merge([$this->author]);
    }

    /**
     * Scope a query to include only the organizations the user belongs to.
     */
    public function scopeUserTeams(Builder $query, $user = null): void
    {
        $user = $user ?: Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $query->whereHas('users', fn ($query) => $query->where('user_id', $user->getKey()))->orWhere('organizations.user_id', $user->getKey());
    }

    /**
     * Purge the team data.
     */
    public function purge(): void
    {
        $this->users()->detach();

        $this->load('visibilityDependents.group');

        $this->visibilityDependents->each(function ($model): void {
            $model->group->organizations()->detach();
            $model->group->users()->detach();
        });
    }
}
