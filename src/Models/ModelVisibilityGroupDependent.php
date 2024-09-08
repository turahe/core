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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Turahe\Core\Database\Factories\ModelVisibilityGroupDependentFactory;

/**
 * Turahe\Core\Models\ModelVisibilityGroupDependent
 *
 * @property-read \Turahe\Core\Models\ModelVisibilityGroup|null $group
 *
 * @method static \Turahe\Core\Database\Factories\ModelVisibilityGroupDependentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroupDependent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroupDependent newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroupDependent query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property int $model_visibility_group_id
 * @property string $dependable_type
 * @property int $dependable_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroupDependent whereDependableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroupDependent whereDependableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroupDependent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ModelVisibilityGroupDependent whereModelVisibilityGroupId($value)
 *
 * @mixin \Eloquent
 */
class ModelVisibilityGroupDependent extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * Indicates if the model has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the visibility dependent model group
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ModelVisibilityGroup::class, 'model_visibility_group_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ModelVisibilityGroupDependentFactory
    {
        return ModelVisibilityGroupDependentFactory::new();
    }
}
