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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Turahe\Core\Models\PinnedTimelineSubject;

trait Timelineable
{
    /**
     * Boot the HasComments trait
     */
    protected static function bootTimelineable(): void
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->pinnedTimelineSubjects()->delete();
            }
        });
    }

    /**
     * Get the timeline pinnable subjects
     */
    public function pinnedTimelineSubjects(): MorphMany
    {
        return $this->morphMany(\Turahe\Core\Models\PinnedTimelineSubject::class, 'timelineable');
    }

    /**
     * Get the timeline pin
     */
    public function getPinnedSubject(string $subjectType, int $subjectKey): ?PinnedTimelineSubject
    {
        return $this->pinnedTimelineSubjects->where('subject_id', $subjectKey)
            ->where('subject_type', $subjectType)
            ->first();
    }

    /**
     * Get the timeline identifier
     */
    public static function timelineKey(): string
    {
        return strtolower(class_basename(get_called_class()));
    }

    /**
     * Get the relation name when the model is used as timelineable
     */
    public function getTimelineRelation(): string
    {
        return Str::plural(strtolower(class_basename(get_called_class())));
    }

    /**
     * Get the timeline component for front-end
     */
    public function getTimelineComponent(): string
    {
        return strtolower(class_basename(get_called_class()));
    }

    /**
     * Get the timeline sort column.
     */
    public function getTimelineSortColumn(): string
    {
        return $this->getCreatedAtColumn();
    }

    /**
     * Apply a scope to include the pinned timeline subjects.
     */
    public function scopeWithPinnedTimelineSubjects(Builder $query, $subject): void
    {
        $pinModel = new PinnedTimelineSubject;

        $callback = function ($join) use ($pinModel, $subject) {
            $join->on($this->getQualifiedKeyName(), '=', $pinModel->getTable().'.timelineable_id')
                ->where($pinModel->getTable().'.timelineable_type', static::class)
                ->where($pinModel->getTable().'.subject_id', $subject->getKey())
                ->where($pinModel->getTable().'.subject_type', $subject::class);
        };

        $query->leftJoin($pinModel->getTable(), $callback);
    }
}
