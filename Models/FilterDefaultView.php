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
 * Modules\Core\Models\FilterDefaultView
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FilterDefaultView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FilterDefaultView newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|FilterDefaultView query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property int $filter_id
 * @property int $user_id
 * @property string $view
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FilterDefaultView whereFilterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FilterDefaultView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FilterDefaultView whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FilterDefaultView whereView($value)
 *
 * @mixin \Eloquent
 */
class FilterDefaultView extends Model
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
    protected $fillable = ['user_id', 'filter_id', 'view'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'user_id'   => 'integer',
        'filter_id' => 'integer',
    ];
}
