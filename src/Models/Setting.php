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
use Illuminate\Database\Eloquent\SoftDeletes;
use Turahe\UserStamps\Concerns\HasUserStamps;

class Setting extends Model
{
    use HasUlids;
    use HasUserStamps;
    use SoftDeletes;

    public $dateFormat = 'U';

    protected $fillable = [
        'model_id',
        'model_type',
        'key',
        'value',
        'group',
    ];

    public function scopeGroup(Builder $query, string $groupName): Builder
    {
        return $query->whereGroup($groupName);
    }
}
