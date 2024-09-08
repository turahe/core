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
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Turahe\Core\Date\Carbon;

/**
 * Turahe\Core\Models\Model
 *
 * @method static Builder|Model newModelQuery()
 * @method static Builder|Model newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|Model query()
 * @method static Builder|Model withCommon()
 *
 * @mixin \Eloquent
 */
class Model extends EloquentModel
{
    /**
     * The columns for the model that are searchable.
     */
    protected static array $searchableColumns = [];

    /**
     * Get the model searchable columns.
     */
    public static function getSearchableColumns(): array
    {
        return static::$searchableColumns;
    }

    /**
     * Set the model searchable columns.
     */
    public static function setSearchableColumns(array $columns): void
    {
        static::$searchableColumns = $columns;
    }

    /**
     * Add new searchable column to the model.
     */
    public static function addSearchableColumn(string|array $column): void
    {
        if (is_array($column)) {
            $key = array_keys($column)[0];
            static::$searchableColumns[$key] = $column[$key];
        } else {
            static::$searchableColumns[] = $column;
        }
    }

    /**
     * Eager load common relationships.
     */
    public function scopeWithCommon(Builder $query): void {}

    /**
     * Apply order to null values to be sorted as last
     */
    public function scopeOrderByNullsLast(Builder $query, string $column, string $direction = 'asc'): Builder
    {
        return $query->orderByRaw(
            $this->getNullsLastSql($query, $column, $direction)
        );
    }

    /**
     * Check whether the model uses the SoftDeletes trait
     */
    public function usesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this::class));
    }

    /**
     * Clear the guardable columns checks cache
     * Used only in unit tests after the custom fields are added
     * As Laravel caches the available columns
     */
    public static function clearGuardableCache(): void
    {
        static::$guardableColumns = [];
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \Turahe\Core\Date\Carbon
     */
    protected function asDateTime($value)
    {
        $value = parent::asDateTime($value);

        return Carbon::instance($value);
    }

    /**
     * Get NULLS LAST SQL.
     */
    protected function getNullsLastSql(Builder $query, string $column, string $direction): string
    {
        /** @var \Illuminate\Database\MySqlConnection */
        $connection = $query->getConnection();

        // Todo, add for sqlite
        $sql = match ($connection->getDriverName()) {
            'mysql', 'mariadb', 'sqlsrv' => 'CASE WHEN :column IS NULL THEN 1 ELSE 0 END, :column :direction',
            'pgsql' => ':column :direction NULLS LAST',
        };

        return str_replace(
            [':column', ':direction'],
            [$column, $direction],
            sprintf($sql, $column, $direction)
        );
    }
}
