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

namespace Modules\Core\Concerns;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/** @mixin \Modules\Core\Models\Model */
trait HasUuid
{
    use HasUuids;

    /**
     * Boot the model uuid generator trait
     */
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (! $model->{static::getUuidColumnName()}) {
                $model->{static::getUuidColumnName()} = static::generateUuid();
            }
        });
    }

    /**
     * Generate model uuid.
     */
    public static function generateUuid(): string
    {
        $uuid = null;

        do {
            if (! static::where(static::getUuidColumnName(), $possible = Str::uuid())->first()) {
                $uuid = $possible;
            }
        } while (! $uuid);

        return $uuid;
    }

    /**
     * Get the model uuid column name.
     */
    protected static function getUuidColumnName(): string
    {
        return 'id';
    }
}
