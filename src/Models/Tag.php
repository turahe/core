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

use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as DatabaseCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Collection;
use Turahe\Core\Concerns\HasDisplayOrder;

/**
 * Turahe\Core\Models\Tag
 *
 * @property int $id
 * @property mixed $name
 * @property mixed $slug
 * @property string|null $type
 * @property int|null $order_column
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Tag containing(string $name)
 * @method static Builder|Tag newModelQuery()
 * @method static Builder|Tag newQuery()
 * @method static Builder|Tag orderByDisplayOrder()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|Tag query()
 * @method static Builder|Tag whereCreatedAt($value)
 * @method static Builder|Tag whereCreatedBy($value)
 * @method static Builder|Tag whereDeletedAt($value)
 * @method static Builder|Tag whereDeletedBy($value)
 * @method static Builder|Tag whereId($value)
 * @method static Builder|Tag whereName($value)
 * @method static Builder|Tag whereOrderColumn($value)
 * @method static Builder|Tag whereSlug($value)
 * @method static Builder|Tag whereType($value)
 * @method static Builder|Tag whereUpdatedAt($value)
 * @method static Builder|Tag whereUpdatedBy($value)
 * @method static Builder|Model withCommon()
 * @method static Builder|Tag withType(?string $type = null)
 *
 * @property string|null $swatch_color
 *
 * @method static Builder|Tag whereSwatchColor($value)
 *
 * @mixin \Eloquent
 */
class Tag extends Model
{
    use HasDisplayOrder;
    use HasUlids;

    public $guarded = [];

    public function scopeWithType(Builder $query, ?string $type = null): Builder
    {
        if (is_null($type)) {
            return $query;
        }

        return $query->where('type', $type);
    }

    public function scopeContaining(Builder $query, string $name): Builder
    {
        return $query->whereRaw('lower('.$this->getQuery()->getGrammar()->wrap('name').') like ?', ['%'.mb_strtolower($name).'%']);
    }

    public static function findOrCreate(
        string|array|ArrayAccess $values,
        ?string $type = null,
    ): Collection|Tag|static {
        $tags = collect($values)->map(function ($value) use ($type) {
            if ($value instanceof self) {
                return $value;
            }

            return static::findOrCreateFromString($value, $type);
        });

        return is_string($values) ? $tags->first() : $tags;
    }

    public static function getWithType(string $type): DatabaseCollection
    {
        return static::withType($type)->get();
    }

    public static function findFromString(string $name, ?string $type = null)
    {
        return static::query()
            ->where('type', $type)
            ->where('name', $name)
            ->first();
    }

    public static function findFromStringOfAnyType(string $name)
    {
        return static::query()
            ->where('name', $name)
            ->get();
    }

    protected static function findOrCreateFromString(string $name, ?string $type = null)
    {
        $tag = static::findFromString($name, $type);

        if (! $tag) {
            $tag = static::create([
                'name' => $name,
                'type' => $type,
                'display_order' => 1000,
            ]);
        }

        return $tag;
    }

    public static function getTypes(): Collection
    {
        return static::groupBy('type')->pluck('type');
    }
}
