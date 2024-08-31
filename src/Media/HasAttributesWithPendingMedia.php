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

namespace Turahe\Core\Media;

use Turahe\Core\EditorPendingMediaProcessor;

/** @mixin \Turahe\Core\Models\Model */
trait HasAttributesWithPendingMedia
{
    /**
     * Boot HasAttributesWithPendingMedia trait
     */
    protected static function bootHasAttributesWithPendingMedia(): void
    {
        static::updated(function ($model) {
            static::runMediaProcessor($model);
        });

        static::created(function ($model) {
            static::runMediaProcessor($model);
        });

        static::deleted(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                static::createMediaProcessor()->deleteAllViaModel(
                    $model,
                    $model->attributesWithPendingMedia()
                );
            }
        });
    }

    /**
     * Get the attributes that may contain pending media
     */
    abstract public function attributesWithPendingMedia(): array|string;

    /**
     * Run the editor media processor
     *
     * @param  \Turahe\Core\Models\Model  $model
     * @return void
     */
    protected static function runMediaProcessor($model)
    {
        static::createMediaProcessor()->processViaModel(
            $model,
            $model->attributesWithPendingMedia()
        );
    }

    /**
     * Create media processor
     */
    protected static function createMediaProcessor(): EditorPendingMediaProcessor
    {
        return new EditorPendingMediaProcessor();
    }
}
