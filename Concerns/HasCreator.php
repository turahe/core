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

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @mixin \Modules\Core\Models\Model */
trait HasCreator
{
    /**
     * Boot HasCreator trait.
     */
    protected static function bootHasCreator(): void
    {
        static::creating(function ($model) {
            $foreignKeyName = (new static)->creator()->getForeignKeyName();

            if (! $model->{$foreignKeyName} && Auth::check()) {
                $model->{$foreignKeyName} = Auth::id();
            }
        });
    }

    /**
     * A model has creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\Modules\Users\Models\User::class, 'created_by');
    }
}
