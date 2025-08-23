<?php

declare(strict_types=1);

namespace Turahe\Core\Concerns;

use Illuminate\Support\Str;

/**
 * Trait that provides configurable primary key behavior
 * based on the userstamps.users_table_column_type configuration.
 *
 * Supported types:
 * - 'bigincrements': Uses auto-incrementing big integers
 * - 'ulid': Uses ULIDs (Universally Unique Lexicographically Sortable Identifier)
 * - 'uuid': Uses UUIDs (Universally Unique Identifier)
 *
 * This trait dynamically provides the appropriate primary key behavior
 * based on the configuration without requiring specific traits.
 */
trait HasConfigurablePrimaryKey
{
    /**
     * Get the configured primary key type based on userstamps configuration.
     */
    public function getConfiguredKeyType(): string
    {
        $columnType = config('userstamps.users_table_column_type', 'ulid');

        return match ($columnType) {
            'bigincrements' => 'int',
            'ulid', 'uuid' => 'string',
            default => 'string',
        };
    }

    /**
     * Check if the model should use auto-incrementing IDs based on configuration.
     */
    public function shouldUseIncrementing(): bool
    {
        $columnType = config('userstamps.users_table_column_type', 'ulid');

        return $columnType === 'bigincrements';
    }

    /**
     * Check if the model should use unique string IDs based on configuration.
     */
    public function shouldUseUniqueIds(): bool
    {
        $columnType = config('userstamps.users_table_column_type', 'ulid');

        return in_array($columnType, ['ulid', 'uuid']);
    }

    /**
     * Get the primary key name.
     */
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * Get the current primary key configuration type.
     */
    public function getPrimaryKeyType(): string
    {
        return config('userstamps.users_table_column_type', 'ulid');
    }

    /**
     * Check if the model uses bigincrements.
     */
    public function usesBigIncrements(): bool
    {
        return $this->getPrimaryKeyType() === 'bigincrements';
    }

    /**
     * Check if the model uses ULIDs.
     */
    public function usesUlids(): bool
    {
        return $this->getPrimaryKeyType() === 'ulid';
    }

    /**
     * Check if the model uses UUIDs.
     */
    public function usesUuids(): bool
    {
        return $this->getPrimaryKeyType() === 'uuid';
    }

    /**
     * Get the data type of the primary key.
     */
    public function getKeyType(): string
    {
        return $this->getConfiguredKeyType();
    }

    /**
     * Get the incrementing status of the primary key.
     */
    public function getIncrementing(): bool
    {
        return $this->shouldUseIncrementing();
    }

    /**
     * Generate a new unique ID based on the configuration.
     */
    public function newUniqueId(): string
    {
        $columnType = config('userstamps.users_table_column_type', 'ulid');

        return match ($columnType) {
            'ulid' => (string) Str::ulid(),
            'uuid' => (string) Str::uuid(),
            default => (string) Str::ulid(), // Fallback to ULID
        };
    }

    /**
     * Get the unique ID columns.
     */
    public function uniqueIds(): array
    {
        if ($this->shouldUseUniqueIds()) {
            return ['id'];
        }

        return [];
    }

    /**
     * Boot the trait and set up dynamic primary key behavior.
     */
    protected static function bootHasConfigurablePrimaryKey(): void
    {
        $columnType = config('userstamps.users_table_column_type', 'ulid');

        if ($columnType === 'ulid') {
            static::creating(function ($model) {
                if (empty($model->getKey())) {
                    $model->setAttribute($model->getKeyName(), $model->newUniqueId());
                }
            });
        } elseif ($columnType === 'uuid') {
            static::creating(function ($model) {
                if (empty($model->getKey())) {
                    $model->setAttribute($model->getKeyName(), $model->newUniqueId());
                }
            });
        }
        // bigincrements doesn't need special handling - database will auto-increment
    }
}
