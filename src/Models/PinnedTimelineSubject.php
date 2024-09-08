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
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Turahe\Core\Models\PinnedTimelineSubject
 *
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property string $timelineable_type
 * @property int $timelineable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $subject
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $timelineable
 *
 * @method static Builder|PinnedTimelineSubject newModelQuery()
 * @method static Builder|PinnedTimelineSubject newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|PinnedTimelineSubject query()
 * @method static Builder|PinnedTimelineSubject whereCreatedAt($value)
 * @method static Builder|PinnedTimelineSubject whereId($value)
 * @method static Builder|PinnedTimelineSubject whereSubjectId($value)
 * @method static Builder|PinnedTimelineSubject whereSubjectType($value)
 * @method static Builder|PinnedTimelineSubject whereTimelineableId($value)
 * @method static Builder|PinnedTimelineSubject whereTimelineableType($value)
 * @method static Builder|PinnedTimelineSubject whereUpdatedAt($value)
 * @method static Builder|Model withCommon()
 *
 * @mixin \Eloquent
 */
class PinnedTimelineSubject extends Model
{
    use HasUlids;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('default_order', function (Builder $builder) {
            $builder->latest();
        });
    }

    /**
     * Pin activity to the given subject
     *
     * @param  int  $subjectId
     * @param  string  $subjectType
     * @param  int  $timelineabeId
     * @param  string  $timelineableType
     * @return \Turahe\Core\Models\PinnedTimelineSubject
     */
    public function pin($subjectId, $subjectType, $timelineabeId, $timelineableType)
    {
        $this->fill([
            'subject_id' => $subjectId,
            'subject_type' => $subjectType,
            'timelineable_id' => $timelineabeId,
            'timelineable_type' => $timelineableType,
        ])->save();

        return $this;
    }

    /**
     * Unpin activity from the given subject
     *
     * @param  int  $subjectId
     * @param  string  $subjectType
     * @param  int  $timelineableId
     * @param  string  $timelineableType
     * @return bool
     */
    public function unpin($subjectId, $subjectType, $timelineableId, $timelineableType)
    {
        $this->where([
            'subject_id' => $subjectId,
            'subject_type' => $subjectType,
            'timelineable_id' => $timelineableId,
            'timelineable_type' => $timelineableType,
        ])->delete();
    }

    /**
     * Get the subject of the pinned timeline
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the timelineable
     */
    public function timelineable(): MorphTo
    {
        return $this->morphTo();
    }
}
