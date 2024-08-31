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

namespace Turahe\Core\Synchronization;

use Turahe\Core\Models\Synchronization;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/** @mixin \Turahe\Core\Models\Model */
trait Synchronizable
{
    /**
     * Get the synchronizable synchronizer class
     *
     * @return \Turahe\Core\Contracts\Synchronization\Synchronizable
     */
    abstract public function synchronizer();

    /**
     * Boot the Synchronizable trait
     *
     * @return void
     */
    public static function bootSynchronizable()
    {
        // Start a new synchronization once created.
        static::created(function ($synchronizable) {
            $synchronizable->synchronization()->create();
        });

        // Stop and delete associated synchronization.
        static::deleting(function ($synchronizable) {
            $synchronizable->synchronization->delete();
        });
    }

    /**
     * Get the model synchronization model
     */
    public function synchronization(): MorphOne
    {
        return $this->morphOne(Synchronization::class, 'synchronizable');
    }
}
