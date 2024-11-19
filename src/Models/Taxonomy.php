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
use Turahe\Media\HasMedia;
use Turahe\UserStamps\Concerns\HasUserStamps;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $code
 * @property string|null $description
 * @property int|null $record_left
 * @property int|null $record_right
 * @property int|null $record_ordering
 * @property string|null $parent_id
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $author
 * @property-read \Kalnoy\Nestedset\Collection<int, Taxonomy> $children
 * @property-read int|null $children_count
 * @property-read \App\Models\User|null $destroyer
 * @property-read \App\Models\User|null $editor
 * @property Taxonomy|null $parent
 *
 * @method static \Kalnoy\Nestedset\Collection<int, static> all($columns = ['*'])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy ancestorsAndSelf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy ancestorsOf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy applyNestedSetScope(?string $table = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy countErrors()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy d()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy defaultOrder(string $dir = 'asc')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy descendantsAndSelf($id, array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy descendantsOf($id, array $columns = [], $andSelf = false)
 * @method static \Database\Factories\TaxonomyFactory factory($count = null, $state = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy fixSubtree($root)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy fixTree($root = null)
 * @method static \Kalnoy\Nestedset\Collection<int, static> get($columns = ['*'])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy getNodeData($id, $required = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy getPlainNodeData($id, $required = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy getTotalErrors()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy hasChildren()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy hasParent()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy isBroken()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy leaves(array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy makeGap(int $cut, int $height)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy moveNode($key, $position)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy newModelQuery()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy newQuery()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy orWhereAncestorOf(bool $id, bool $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy orWhereDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy orWhereNodeBetween($values)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy orWhereNotDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy ordered(string $direction = 'asc')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy query()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy rebuildSubtree($root, array $data, $delete = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy rebuildTree(array $data, $delete = false, $root = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy reversed()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy root(array $columns = [])
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy searchable()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy visible()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereAncestorOf($id, $andSelf = false, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereAncestorOrSelf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereCode($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereCreatedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereCreatedBy($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereDeletedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereDeletedBy($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereDescendantOf($id, $boolean = 'and', $not = false, $andSelf = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereDescendantOrSelf(string $id, string $boolean = 'and', string $not = false)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereDescription($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereId($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereIsAfter($id, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereIsBefore($id, $boolean = 'and')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereIsLeaf()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereIsRoot()
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereName($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereNodeBetween($values, $boolean = 'and', $not = false, $query = null)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereNotDescendantOf($id)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereParentId($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereRecordLeft($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereRecordOrdering($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereRecordRight($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereSlug($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereUpdatedAt($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy whereUpdatedBy($value)
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy withDepth(string $as = 'depth')
 * @method static \Kalnoy\Nestedset\QueryBuilder|Taxonomy withoutRoot()
 * @method static Builder|Taxonomy onlyTrashed()
 * @method static Builder|Taxonomy withTrashed()
 * @method static Builder|Taxonomy withoutTrashed()
 *
 * @property-read \Kalnoy\Nestedset\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 *
 * @mixin \Eloquent
 */
class Taxonomy extends Model implements Sortable
{
    use HasMedia;
    use HasSlug;
    use HasUlids;
    use HasUserStamps;
    use NodeTrait;
    use Prunable;
    use SoftDeletes;
    use SortableTrait;

    protected $table = 'taxonomies';

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
    ];

    /**
     * @var string
     */
    public $dateFormat = 'U';

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
