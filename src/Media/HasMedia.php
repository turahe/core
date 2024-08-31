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

use Illuminate\Support\Str;

/** @mixin \Turahe\Core\Models\Model */
trait HasMedia
{
    use \Turahe\Media\HasMedia;

    /**
     * Boot HasMedia trait
     */
    protected static function bootHasMedia(): void
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->purgeMedia();
            }
        });
    }

    /**
     * Purge the model media
     *
     * The function deletes the files and the storage folder when empty
     */
    public function purgeMedia(): void
    {
        $this->media()->get()->each->delete();
    }

    /**
     * Get the model media directory
     */
    public function getMediaDirectory(): string
    {
        $folder = Str::kebab(class_basename(get_called_class()));

        return config('core.media.directory').DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$this->id;
    }

    /**
     * Get the model media tag
     */
    public function getMediaTags(): array
    {
        return ['profile'];
    }
}
