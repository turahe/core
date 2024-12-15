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
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Turahe\Core\Concerns\HasSettings;
use Turahe\Core\Concerns\HasTaxonomies;
use Turahe\Core\Enums\OrganizationType;
use Turahe\UserStamps\Concerns\HasUserStamps;

class Organization extends Model implements Sortable
{
    use HasSettings;
    use HasSlug;
    use HasTaxonomies;
    use HasUlids;
    use HasUserStamps;
    use Prunable;
    use SoftDeletes;
    use SortableTrait;

    public $dateFormat = 'U';

    protected $fillable = [
        'name',
        'code',
        'type',
        'slug',
    ];

    protected $table = 'organizations';

    /**
     * @return string
     */
    public function getLftName()
    {
        return 'record_left';
    }

    /**
     * @return string
     */
    public function getRgtName()
    {
        return 'record_right';
    }

    /**
     * @return string
     */
    public function getParentIdName()
    {
        return 'parent_id';
    }

    /**
     * Specify parent id attribute mutator
     *
     *
     * @throws \Exception
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
            User::class,
            'model',
            'model_has_organization',
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
    public function scopeUserTeams(Builder $query, ?User $user = null): void
    {
        /** @var User $user */
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
