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

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Turahe\Core\Models\Media
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $file_name
 * @property string $disk
 * @property string $mime_type
 * @property int $size
 * @property int|null $record_left
 * @property int|null $record_right
 * @property int|null $record_ordering
 * @property int|null $parent_id
 * @property string|null $custom_attribute
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Kalnoy\Nestedset\Collection<int, Media> $children
 * @property-read int|null $children_count
 * @property-read string $extension
 * @property-read string|null $type
 * @property Media|null $parent
 * @property-read \Turahe\Core\Models\PendingMedia|null $pendingData
 *
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media ancestorsAndSelf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media ancestorsOf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media applyNestedSetScope(?string $table = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media byToken(string $token)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media countErrors()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media d()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media defaultOrder(string $dir = 'asc')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media descendantsAndSelf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media descendantsOf($id, array $columns = [], $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media fixSubtree($root)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media fixTree($root = null)
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media getNodeData($id, $required = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media getPlainNodeData($id, $required = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media getTotalErrors()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media hasChildren()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media hasParent()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media isBroken()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media leaves(array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media makeGap(int $cut, int $height)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media moveNode($key, $position)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media newModelQuery()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media newQuery()
 * @method static Builder|Media onlyTrashed()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media orWhereAncestorOf(bool $id, bool $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media orWhereDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media orWhereNodeBetween($values)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media orWhereNotDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media query()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media rebuildSubtree($root, array $data, $delete = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media rebuildTree(array $data, $delete = false, $root = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media reversed()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media root(array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereAncestorOf($id, $andSelf = false, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereAncestorOrSelf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereCreatedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereCreatedBy($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereCustomAttribute($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereDeletedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereDeletedBy($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereDescendantOf($id, $boolean = 'and', $not = false, $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereDescendantOrSelf(string $id, string $boolean = 'and', string $not = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereDisk($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereFileName($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereId($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereIsAfter($id, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereIsBefore($id, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereIsLeaf()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereIsRoot()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereMimeType($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereName($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereNodeBetween($values, $boolean = 'and', $not = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereNotDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereParentId($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereRecordLeft($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereRecordOrdering($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereRecordRight($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereSize($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereUpdatedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereUpdatedBy($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereUuid($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media withDepth(string $as = 'depth')
 * @method static Builder|Media withTrashed()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media withoutRoot()
 * @method static Builder|Media withoutTrashed()
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 *
 * @property int|null $record_dept
 *
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Media whereRecordDept($value)
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 *
 * @mixin \Eloquent
 */
class Media extends \Turahe\Media\Models\Media
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media';

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        /**
         * When creating new media, we will add random key identifier
         */
        static::creating(function ($model) {
            $model->token = Str::uuid()->toString();

            return $model;
        });

        /**
         * On media deleted, remove the created folder for the resource
         */
        static::deleted(function ($model) {
            tap(Storage::disk($model->disk), function ($disk) use ($model) {
                $files = $disk->files($model->directory);

                if (count($files) === 0) {
                    $disk->deleteDirectory($model->directory);
                }
            });
        });
    }

    /**
     * Mark the current media instance as pending media.
     */
    public function markAsPending(string $draftId): PendingMedia
    {
        $pendingModel = new PendingMedia(['media_id' => $this->id, 'draft_id' => $draftId]);

        $pendingModel->save();

        return $pendingModel;
    }

    /**
     * A media may be pending
     */
    public function pendingData(): BelongsTo
    {
        return $this->belongsTo(PendingMedia::class, 'id', 'media_id');
    }

    /**
     * Check whether the media video is HTML5 supported video
     *
     * @see https://www.w3schools.com/html/html5_video.asp
     */
    public function isHtml5SupportedVideo(): bool
    {
        return in_array($this->extension, ['mp4', 'webm', 'ogg']);
    }

    /**
     * Check whether the media audio is HTML5 supported audio
     *
     * @see https://www.w3schools.com/html/html5_audio.asp
     */
    public function isHtml5SupportedAudio(): bool
    {
        return in_array($this->extension, ['mp3', 'wav', 'ogg']);
    }

    /**
     * Get the media item view URL
     */
    public function getViewUrl(): string
    {
        return url("/media/{$this->token}");
    }

    /**
     * Get the media item preview URL
     */
    public function getPreviewUrl(): string
    {
        return url($this->previewPath());
    }

    /**
     * Get the media item preview URL
     */
    public function getDownloadUrl(): string
    {
        return url($this->downloadPath());
    }

    /**
     * Get the media item download URI
     */
    public function downloadPath(): string
    {
        return "/media/{$this->token}/download";
    }

    /**
     * Get the media item preview URI
     */
    public function previewPath(): string
    {
        return "/media/{$this->token}/preview";
    }

    /**
     * Scope a query to only include media by given token.
     */
    public function scopeByToken(Builder $query, string $token): void
    {
        $query->where('token', $token);
    }

    /**
     *  Delete model media by id's
     */
    public function purgeByMediableIds(string $mediable, iterable $ids): bool
    {
        if (count($ids) === 0) {
            return false;
        }

        $this->whereIn('id', fn ($query) => $query->select('media_id')
            ->from(config('mediable.mediables_table'))
            ->where('mediable_type', $mediable)
            ->whereIn('mediable_id', $ids))
            ->get()->each->delete();

        return true;
    }
}
