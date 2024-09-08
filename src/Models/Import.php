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
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Fields\FieldsCollection;

/**
 * Turahe\Core\Models\Import
 *
 * @property-read \Turahe\Users\Models\User $user
 *
 * @method static Builder|Import byResource(string $resourceName)
 * @method static Builder|Import inProgress()
 * @method static Builder|Import newModelQuery()
 * @method static Builder|Import newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|Import query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $file_path
 * @property string|null $skip_file_path
 * @property string $resource_name
 * @property int $status
 * @property array|null $data
 * @property int $imported
 * @property int $skipped
 * @property int $duplicates
 * @property int|null $user_id
 * @property string|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Import whereCompletedAt($value)
 * @method static Builder|Import whereCreatedAt($value)
 * @method static Builder|Import whereData($value)
 * @method static Builder|Import whereDuplicates($value)
 * @method static Builder|Import whereFilePath($value)
 * @method static Builder|Import whereId($value)
 * @method static Builder|Import whereImported($value)
 * @method static Builder|Import whereResourceName($value)
 * @method static Builder|Import whereSkipFilePath($value)
 * @method static Builder|Import whereSkipped($value)
 * @method static Builder|Import whereStatus($value)
 * @method static Builder|Import whereUpdatedAt($value)
 * @method static Builder|Import whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Import extends Model
{
    use HasUlids;

    const STATUSES = [
        'mapping' => 1,
        'in-progress' => 2,
        'finished' => 3,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_path',
        'skip_file_path',
        'resource_name',
        'status',
        'imported',
        'skipped',
        'duplicates',
        'user_id',
        'completed_at',
        'data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'user_id' => 'int',
        'duplicates' => 'int',
        'skipped' => 'int',
        'imported' => 'int',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function ($model) {
            Storage::disk(static::disk())->deleteDirectory($model->storagePath());
        });
    }

    /**
     * Scope a query to only include imports of a given resource.
     */
    public function scopeByResource(Builder $query, string $resourceName): void
    {
        $query->where('resource_name', $resourceName);
    }

    /**
     * Scope a query to only include imports with status in progress.
     */
    public function scopeInProgress(Builder $query): void
    {
        $query->where('status', 'in-progress');
    }

    /**
     * Remove import file from storage
     */
    public function removeFile(string $path): bool
    {
        $disk = Storage::disk(static::disk());

        if ($disk->exists($path)) {
            return $disk->delete($path);
        }

        return false;
    }

    /**
     * Get the import files storage path
     *
     * Should be used once the model has been created and  the file is uploaded as it's
     * using the folder from the initial upload files, all other files will be stored there as well
     */
    public function storagePath(string $glue = ''): string
    {
        $path = $this->id ?
            pathinfo($this->file_path, PATHINFO_DIRNAME) :
            'imports'.DIRECTORY_SEPARATOR.Str::random(15);

        return $path.($glue ? (DIRECTORY_SEPARATOR.$glue) : '');
    }

    /**
     * An Import has user/creator
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\Turahe\Users\Models\User::class);
    }

    /**
     * Get the fields intended for this import
     */
    public function fields(): FieldsCollection
    {
        Innoclapps::setImportStatus('mapping');

        return Innoclapps::resourceByName($this->resource_name)
            ->importable()
            ->resolveFields();
    }

    /**
     * Get the file name attribute
     */
    public function fileName(): Attribute
    {
        return Attribute::get(fn () => basename($this->file_path));
    }

    /**
     * Get the skip file filename name attribute
     */
    public function skipFileFilename(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->skip_file_path) {
                return null;
            }

            return basename($this->skip_file_path);
        });
    }

    /**
     * Get the import storage disk
     */
    public static function disk(): string
    {
        return 'local';
    }

    /**
     * Get the import's status.
     */
    protected function status(): Attribute
    {
        return new Attribute(
            get: fn ($value) => array_search($value, static::STATUSES),
            set: fn ($value) => static::STATUSES[
                is_numeric($value) ? array_search($value, static::STATUSES) : $value
            ]
        );
    }
}
