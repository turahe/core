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

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\Core\Models\PendingMedia
 *
 * @property-read \Modules\Core\Models\Media $attachment
 *
 * @method static Builder|PendingMedia newModelQuery()
 * @method static Builder|PendingMedia newQuery()
 * @method static Builder|PendingMedia ofDraftId(string $draftId)
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|PendingMedia query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $draft_id
 * @property int $media_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|PendingMedia whereCreatedAt($value)
 * @method static Builder|PendingMedia whereDraftId($value)
 * @method static Builder|PendingMedia whereId($value)
 * @method static Builder|PendingMedia whereMediaId($value)
 * @method static Builder|PendingMedia whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PendingMedia extends Model
{
    use HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pending_media_attachments';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'media_id' => 'int',
    ];

    /**
     * A pending media has attachment/media
     */
    public function attachment(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Media::class, 'media_id');
    }

    /**
     * Scope a query to only include pending media by given draft id.
     */
    public function scopeOfDraftId(Builder $query, string $draftId): void
    {
        $query->where('draft_id', $draftId);
    }

    /**
     * Purge the pending media.
     */
    public function purge(): bool
    {
        $this->attachment->delete();

        return $this->delete();
    }
}
