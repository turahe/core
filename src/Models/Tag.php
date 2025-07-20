<?php

declare(strict_types=1);

namespace Turahe\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Turahe\UserStamps\Concerns\HasUserStamps;

class Tag extends Model implements Sortable
{
    use HasSlug;
    use HasUlids;
    use HasUserStamps;
    use SoftDeletes;
    use SortableTrait;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('core.tables.tags');
    }

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
    ];

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
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /**
     * Scope with type
     */
    public function scopeWithType(Builder $query, ?string $type = null): Builder
    {
        if (is_null($type)) {
            return $query;
        }

        return $query->where('type', $type)->ordered();
    }

    /**
     * Scope with containing name
     */
    public function scopeContaining(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%'.mb_strtolower($name).'%');
    }

    /**
     * find or create tags
     */
    public static function findOrCreate(string|array|\ArrayAccess $values, ?string $type = null): Collection|self
    {
        $tags = collect($values)->map(function ($value) use ($type) {
            if ($value instanceof self) {
                return $value;
            }

            return static::findOrCreateFromString($value, $type);
        });

        return is_string($values) ? $tags->first() : $tags;
    }

    /**
     * Get with type
     */
    public static function getWithType(string $type): DbCollection
    {
        return static::withType($type)->get();
    }

    /**
     * find tag use name or slug
     */
    public static function findFromString(string $name, ?string $type = null): ?Tag
    {

        return static::query()
            ->where('type', $type)
            ->where(function ($query) use ($name): void {
                $query->where('name', $name)
                    ->orWhere('slug', $name);
            })
            ->first();
    }

    /**
     * find tag from string of any type name or slug tags
     */
    public static function findFromStringOfAnyType(string $name): DbCollection
    {
        return static::query()
            ->where('name', $name)
            ->orWhere('slug', $name)
            ->get();
    }

    /**
     * find tag from string of any type name tag
     */
    public static function findOrCreateFromString(string $name, ?string $type = null): ?Tag
    {
        $tag = static::findFromString($name, $type);

        if (! $tag) {
            $tag = static::create([
                'name' => $name,
                'type' => $type,
            ]);
        }

        return $tag;
    }

    /**
     * Get all tags type
     */
    public static function getTypes(): Collection
    {
        return static::groupBy('type')->pluck('type');
    }
}
