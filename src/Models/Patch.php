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

/**
 * Turahe\Core\Models\Patch
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Patch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Patch newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Patch query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $token
 * @property string $version
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Patch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patch whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Patch whereVersion($value)
 *
 * @mixin \Eloquent
 */
class Patch extends Model
{
    protected $guarded = [];
}
