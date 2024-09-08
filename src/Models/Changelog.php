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

use Spatie\Activitylog\Models\Activity as SpatieActivityLog;
use Turahe\Core\Timeline\Timelineable;

/**
 * Turahe\Core\Models\Changelog
 *
 * @property int $id
 * @property string|null $log_name
 * @property string $description
 * @property string|null $subject_type
 * @property string|null $event
 * @property int|null $subject_id
 * @property string|null $causer_type
 * @property int|null $causer_id
 * @property \Illuminate\Support\Collection|null $properties
 * @property string|null $batch_uuid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $causer
 * @property-read \Illuminate\Support\Collection $changes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Turahe\Core\Models\PinnedTimelineSubject> $pinnedTimelineSubjects
 * @property-read int|null $pinned_timeline_subjects_count
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $subject
 *
 * @method static Builder|Activity causedBy(\Illuminate\Database\Eloquent\Model $causer)
 * @method static Builder|Activity forBatch(string $batchUuid)
 * @method static Builder|Activity forEvent(string $event)
 * @method static Builder|Activity forSubject(\Illuminate\Database\Eloquent\Model $subject)
 * @method static Builder|Activity hasBatch()
 * @method static Builder|Activity inLog(...$logNames)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog query()
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereBatchUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereCauserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereCauserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereLogName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog withPinnedTimelineSubjects($subject)
 *
 * @property string|null $identifier
 * @property string|null $causer_name
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereCauserName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Changelog whereIdentifier($value)
 *
 * @mixin \Eloquent
 */
class Changelog extends SpatieActivityLog
{
    use Timelineable;

    /**
     * Latests saved activity
     *
     * @var null|\Turahe\Core\Models\Changelog
     */
    public static $latestSavedLog;

    /**
     * Causer names cache, when importing a lot data helps making hundreds of queries.
     */
    protected static array $causerNames = [];

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        /**
         * Automatically set the causer name if the logged in
         * user is set and no causer name provided
         */
        static::creating(function ($model) {
            if (! $model->causer_name) {
                $model->causer_name = static::causerName($model);
            }
        });

        static::created(function ($model) {
            static::$latestSavedLog = $model;
        });
    }

    /**
     * Get the causer name for the given model
     *
     * @param  \Turahe\Core\Models\Changelog  $model
     * @return string|null
     */
    protected static function causerName($model)
    {
        if ($model->causer_id) {
            if (isset(static::$causerNames[$model->causer_id])) {
                return static::$causerNames[$model->causer_id];
            }

            return static::$causerNames[$model->causer_id] = $model->causer?->name;
        }

        return auth()->user()?->name;
    }

    /**
     * Get the relation name when the model is used as activity
     */
    public function getTimelineRelation(): string
    {
        return 'changelog';
    }

    /**
     * Get the activity front-end component
     */
    public function getTimelineComponent(): string
    {
        return $this->identifier;
    }
}
