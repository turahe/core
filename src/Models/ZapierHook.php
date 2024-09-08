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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Turahe\Core\Models\ZapierHook
 *
 * @property-read \Turahe\Users\Models\User $user
 *
 * @method static Builder|ZapierHook byResource(string $resourceName)
 * @method static Builder|ZapierHook newModelQuery()
 * @method static Builder|ZapierHook newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|ZapierHook query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $hook
 * @property string $resource_name
 * @property string $action
 * @property array|null $data
 * @property int $zap_id
 * @property int $user_id
 *
 * @method static Builder|ZapierHook whereAction($value)
 * @method static Builder|ZapierHook whereData($value)
 * @method static Builder|ZapierHook whereHook($value)
 * @method static Builder|ZapierHook whereId($value)
 * @method static Builder|ZapierHook whereResourceName($value)
 * @method static Builder|ZapierHook whereUserId($value)
 * @method static Builder|ZapierHook whereZapId($value)
 *
 * @mixin \Eloquent
 */
class ZapierHook extends Model
{
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['hook', 'action', 'resource_name', 'data', 'user_id', 'zap_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'user_id' => 'int',
        'zap_id' => 'int',
    ];

    /**
     * Indicates if the model has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * A hook belongs to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Turahe\Users\Models\User::class);
    }

    /**
     * Scope a query to only include imports of a given resource.
     */
    public function scopeByResource(Builder $query, string $resourceName): void
    {
        $query->where('resource_name', $resourceName);
    }
}
