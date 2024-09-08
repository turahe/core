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

namespace Turahe\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Turahe\Core\Database\Factories\ModelVisibilityGroupFactory;

/**
 * Turahe\Core\Models\ModelVisibilityGroup
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Turahe\Users\Models\Organization> $organizations
 * @property-read int|null $organizations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Turahe\Users\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $visibilityable
 *
 * @method static \Turahe\Core\Database\Factories\ModelVisibilityGroupFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroup newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroup query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $type
 * @property string $visibilityable_type
 * @property int $visibilityable_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroup whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroup whereVisibilityableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroup whereVisibilityableType($value)
 *
 * @mixin \Eloquent
 */
class ModelVisibilityGroup extends Model
{
    use HasFactory;
    use HasUlids;

    protected string $dependsTable = 'model_visibility_group_dependents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type'];

    /**
     * Indicates if the model has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get all of the organizations that belongs to the visibility group
     */
    public function organizations(): MorphToMany
    {
        return $this->morphedByMany(\Turahe\Users\Models\Organization::class, 'dependable', $this->dependsTable);
    }

    /**
     * Get all of the users that belongs to the visibility group
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(\Turahe\Users\Models\User::class, 'dependable', $this->dependsTable);
    }

    /**
     * Get the parent model which uses visibility dependents
     */
    public function visibilityable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ModelVisibilityGroupFactory
    {
        return ModelVisibilityGroupFactory::new();
    }
}
