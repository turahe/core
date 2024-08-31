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

use Turahe\Core\Concerns\HasCreator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

/**
 * Turahe\Core\Models\Workflow
 *
 * @property-read \Turahe\Users\Models\User $creator
 *
 * @method static Builder|Workflow active()
 * @method static Builder|Workflow byTrigger(string $triggerType)
 * @method static Builder|Workflow newModelQuery()
 * @method static Builder|Workflow newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|Workflow query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $trigger_type
 * @property string $action_type
 * @property array|null $data
 * @property bool $is_active
 * @property int $total_executions
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Workflow whereActionType($value)
 * @method static Builder|Workflow whereCreatedAt($value)
 * @method static Builder|Workflow whereCreatedBy($value)
 * @method static Builder|Workflow whereData($value)
 * @method static Builder|Workflow whereDescription($value)
 * @method static Builder|Workflow whereId($value)
 * @method static Builder|Workflow whereIsActive($value)
 * @method static Builder|Workflow whereTitle($value)
 * @method static Builder|Workflow whereTotalExecutions($value)
 * @method static Builder|Workflow whereTriggerType($value)
 * @method static Builder|Workflow whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Workflow extends Model
{
    use HasCreator;
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'trigger_type', 'action_type', 'data', 'created_by', 'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'             => 'array',
        'is_active'        => 'boolean',
        'total_executions' => 'int',
        'created_by'       => 'int',
    ];

    /**
     * Scope a query to only include workflows of a given trigger type.
     */
    public function scopeByTrigger(Builder $query, string $triggerType): void
    {
        $query->where('trigger_type', $triggerType);
    }

    /**
     * Scope a query to only include active workflows.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', 1);
    }
}
