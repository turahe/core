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

use Illuminate\Support\Facades\Lang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\Core\Database\Factories\FilterFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modules\Core\Models\Filter
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Core\Models\FilterDefaultView> $defaults
 * @property-read int|null $defaults_count
 * @property-read \Modules\Users\Models\User $user
 *
 * @method static \Modules\Core\Database\Factories\FilterFactory factory($count = null, $state = [])
 * @method static Builder|Filter hasDefaultFor(string $identifier, string $view, int $userId)
 * @method static Builder|Filter newModelQuery()
 * @method static Builder|Filter newQuery()
 * @method static Builder|Filter ofIdentifier(string $identifier)
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|Filter query()
 * @method static Builder|Filter shared()
 * @method static Builder|Filter visibleFor(int $userId)
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $name
 * @property string $identifier
 * @property int|null $user_id
 * @property bool $is_shared
 * @property bool $is_readonly
 * @property array $rules
 * @property string|null $flag
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Filter whereCreatedAt($value)
 * @method static Builder|Filter whereFlag($value)
 * @method static Builder|Filter whereId($value)
 * @method static Builder|Filter whereIdentifier($value)
 * @method static Builder|Filter whereIsReadonly($value)
 * @method static Builder|Filter whereIsShared($value)
 * @method static Builder|Filter whereName($value)
 * @method static Builder|Filter whereRules($value)
 * @method static Builder|Filter whereUpdatedAt($value)
 * @method static Builder|Filter whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Filter extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'identifier', 'rules', 'is_shared', 'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'rules'       => 'array',
        'is_shared'   => 'boolean',
        'is_readonly' => 'boolean',
        'user_id'     => 'int',
    ];

    /**
     * Get the filter owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class);
    }

    /**
     * Filter has many default views
     */
    public function defaults(): HasMany
    {
        return $this->hasMany(FilterDefaultView::class);
    }

    /**
     * Indicates whether the filter is system default
     */
    public function isSystemDefault(): Attribute
    {
        return Attribute::get(fn () => is_null($this->user_id));
    }

    /**
     * Name attribute accessor
     *
     * Supports translation from language file
     */
    public function name(): Attribute
    {
        return Attribute::get(function (string $value, array $attributes) {
            if (! array_key_exists('id', $attributes)) {
                return $value;
            }

            $customKey = 'custom.filter.'.$attributes['id'];

            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }

    /**
     * Set rules attribute mutator
     *
     * We will check if the passed value is array and there are
     * children defined in the array, if not, we will assume the the
     * children is passed as one big array
     */
    public function rules(): Attribute
    {
        return Attribute::set(function ($value) {
            if (is_array($value) && ! array_key_exists('children', $value)) {
                $value = [
                    'condition' => 'and',
                    'children'  => $value,
                ];
            }

            return json_encode(is_array($value) ? $value : []);
        });
    }

    /**
     * Mark filter as default for the given user.
     */
    public function markAsDefault(string|array $views, int $userId): static
    {
        foreach ((array) $views as $view) {
            // We will check if there is current default filter for the view and the user
            // If yes, we will remove this default filter to leave space for the new one
            if ($currentDefault = static::hasDefaultFor($this->identifier, $view, $userId)->first()) {
                $currentDefault->unMarkAsDefault($view, $userId);
            }

            $this->defaults()->create(['user_id' => $userId,  'view' => $view]);
        }

        return $this;
    }

    /**
     * Unmark filter as default for the given user.
     */
    public function unMarkAsDefault(string|array $views, int $userId): static
    {
        foreach ((array) $views as $view) {
            $this->defaults()
                ->where('view', $view)
                ->where('user_id', $userId)
                ->delete();
        }

        return $this;
    }

    /**
     * Scope a query to include default filters for the given filterables.
     */
    public function scopeHasDefaultFor(Builder $query, string $identifier, string $view, int $userId): void
    {
        $query->whereHas('defaults', function ($query) use ($view, $userId) {
            return $query->where('view', $view)->where('user_id', $userId);
        })->ofIdentifier($identifier)->visibleFor($userId);
    }

    /**
     * Scope a query to only include shared filters.
     */
    public function scopeShared(Builder $query): void
    {
        $query->where('is_shared', true);
    }

    /**
     * Find filter by flag.
     */
    public static function findByFlag(string $flag): ?Filter
    {
        return static::where('flag', $flag)->first();
    }

    /**
     * Scope a query to only include filters of the given identifier.
     */
    public function scopeOfIdentifier(Builder $query, string $identifier): void
    {
        $query->where('identifier', $identifier);
    }

    /**
     * Scope a query to only include filters visible for the given user.
     */
    public function scopeVisibleFor(Builder $query, int $userId): void
    {
        $query->where(function ($query) use ($userId) {
            return $query->where('filters.user_id', $userId)
                ->orWhere('is_shared', true)
                ->orWhereNull('user_id');
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): FilterFactory
    {
        return new FilterFactory;
    }
}
