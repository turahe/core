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

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;

/**
 * Modules\Core\Models\UserOrderedModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserOrderedModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserOrderedModel newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|UserOrderedModel query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property int $display_order
 * @property int $user_id
 * @property string $orderable_type
 * @property int $orderable_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserOrderedModel whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserOrderedModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserOrderedModel whereOrderableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserOrderedModel whereOrderableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserOrderedModel whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserOrderedModel extends Model
{
    use HasUlids;

    /**
     * Indicates if the model has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['display_order', 'user_id'];
}
