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

use Modules\Core\Facades\Cards;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Database\Factories\DashboardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Wildside\Userstamps\Userstamps;

/**
 * Modules\Core\Models\Dashboard
 *
 * @property-read \Modules\Users\Models\User $user
 *
 * @method static Builder|Dashboard byUser(int $userId)
 * @method static \Modules\Core\Database\Factories\DashboardFactory factory($count = null, $state = [])
 * @method static Builder|Dashboard newModelQuery()
 * @method static Builder|Dashboard newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|Dashboard query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $name
 * @property bool $is_default
 * @property array $cards
 * @property int $user_id Owner
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Dashboard whereCards($value)
 * @method static Builder|Dashboard whereCreatedAt($value)
 * @method static Builder|Dashboard whereId($value)
 * @method static Builder|Dashboard whereIsDefault($value)
 * @method static Builder|Dashboard whereName($value)
 * @method static Builder|Dashboard whereUpdatedAt($value)
 * @method static Builder|Dashboard whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Dashboard extends Model
{
    use HasFactory;
    use HasUlids;
    use Userstamps;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'cards'      => 'array',
        'is_default' => 'boolean',
        'user_id'    => 'int',
    ];

    /**
     * Boot the model.
     */
    public static function boot(): void
    {
        parent::boot();

        static::created(static::handleMarkedAsDefault(...));
        static::updated(static::handleMarkedAsDefault(...));
    }

    /**
     * Handle dashboard marked as default.
     */
    protected static function handleMarkedAsDefault(Dashboard $model): void
    {
        if (($model->wasChanged('is_default') || $model->wasRecentlyCreated) && $model->is_default === true) {
            static::query()->where('id', '!=', $model->id)->update(['is_default' => false]);
        }
    }

    /**
     * Scope a query dashboards for the given user.
     */
    public function scopeByUser(Builder $query, string $userId): void
    {
        $query->where('created_by', $userId);
    }


    /**
     * Get the default available dashboard cards
     *
     * @param  \Modules\Users\Models\User|null  $user
     * @return \Illuminate\Support\Collection
     */
    public static function defaultCards($user = null)
    {
        return Cards::registered()->filter->authorizedToSee($user)
            ->reject(fn ($card) => $card->onlyOnIndex === true)
            ->values()
            ->map(function ($card, $index) {
                return ['key' => $card->uriKey(), 'order' => $index + 1];
            });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DashboardFactory
    {
        return DashboardFactory::new();
    }
}
