<?php

declare(strict_types=1);
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2023.
 *
 *
 */

namespace Turahe\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Turahe\UserStamps\Concerns\HasUserStamps;

class Taxonomy extends Model implements Sortable
{
    use HasSlug;
    use HasUlids;
    use HasUserStamps;
    use NodeTrait;
    use Prunable;
    use SoftDeletes;
    use SortableTrait;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('core.tables.taxonomies');
    }

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
    ];

    /**
     * @return string
     */
    public function getLftName()
    {
        return 'record_left';
    }

    /**
     * @return string
     */
    public function getRgtName()
    {
        return 'record_right';
    }

    /**
     * @return string
     */
    public function getParentIdName()
    {
        return 'parent_id';
    }

    /**
     * Specify parent id attribute mutator
     *
     * @throws \Exception
     */
    public function setParentAttribute($value): void
    {
        $this->setParentIdAttribute($value);
    }

    /**
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'record_ordering',
        'sort_when_creating' => true,
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Scope visible taxonomies.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Scope searchable taxonomies.
     */
    public function scopeSearchable(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }
}
