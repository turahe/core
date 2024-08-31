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

namespace Turahe\Core\Timeline;

use Turahe\Core\Models\PinnedTimelineSubject;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTimeline
{
    /**
     * Boot the HasTimeline trait
     */
    protected static function bootHasTimeline(): void
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->pinnedTimelineables->each->delete();
            }
        });
    }

    /**
     * Get the timeline subject key
     */
    public static function getTimelineSubjectKey(): string
    {
        return strtolower(class_basename(get_called_class()));
    }

    /**
     * Get the subject pinned timelineables models
     */
    public function pinnedTimelineables(): MorphMany
    {
        return $this->morphMany(PinnedTimelineSubject::class, 'subject');
    }
}
