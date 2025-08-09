<?php

declare(strict_types=1);

namespace Turahe\Core\Concerns;

use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Turahe\Core\Models\Tag;

trait HasTags
{
    /**
     * Queued tags for batch processing
     */
    protected array $queuedTags = [];
    
    /**
     * Cached tags collection for performance
     */
    private ?Collection $_tagsCache = null;

    /**
     * Boot the HasTags trait with enhanced PHP 8.4 features
     */
    public static function bootHasTags(): void
    {
        static::created(function (Model $taggableModel): void {
            // Using array spread for better performance check
            if (empty([...$taggableModel->queuedTags])) {
                return;
            }

            $taggableModel->attachTags($taggableModel->queuedTags);
            $taggableModel->queuedTags = [];
            $taggableModel->clearTagsCache();
        });

        static::deleted(function (Model $deletedModel): void {
            $tags = $deletedModel->tags()->get();
            $deletedModel->detachTags($tags);
            $deletedModel->clearTagsCache();
        });
        
        static::saved(function (Model $savedModel): void {
            $savedModel->clearTagsCache();
        });
    }

    public function tags(): MorphToMany
    {
        return $this
            ->morphToMany(Tag::class, 'taggable', config('core.tables.taggables'))
            ->using(MorphPivot::class)
            ->ordered();
    }

    public function setTagsAttribute(string|array|ArrayAccess|Tag $tags): void
    {
        if (! $this->exists) {
            $this->queuedTags = $tags;

            return;
        }

        $this->syncTags($tags);
    }

    /**
     * Scope with all tags using PHP 8.4 enhanced performance
     */
    public function scopeWithAllTags(Builder $query, string|array|ArrayAccess|Tag $tags, ?string $type = null): Builder
    {
        $tags = static::convertToTags($tags, $type);

        // Using array spread for better performance
        collect([...$tags])->each(function ($tag) use ($query): void {
            $query->whereHas('tags', fn (Builder $q) => $q->where('tags.id', $tag->id ?? 0));
        });

        return $query;
    }

    public function scopeWithAnyTags(Builder $query, string|array|ArrayAccess|Tag $tags, ?string $type = null): Builder
    {
        $tags = static::convertToTags($tags, $type);

        return $query
            ->whereHas('tags', function (Builder $query) use ($tags): void {
                $tagIds = collect($tags)->pluck('id');

                $query->whereIn('tags.id', $tagIds);
            });
    }

    public function scopeWithoutTags(Builder $query, string|array|ArrayAccess|Tag $tags, ?string $type = null): Builder
    {
        $tags = static::convertToTags($tags, $type);

        return $query
            ->whereDoesntHave('tags', function (Builder $query) use ($tags): void {
                $tagIds = collect($tags)->pluck('id');

                $query->whereIn('tags.id', $tagIds);
            });
    }

    public function scopeWithAllTagsOfAnyType(Builder $query, $tags): Builder
    {
        $tags = static::convertToTagsOfAnyType($tags);

        collect($tags)
            ->each(function ($tag) use ($query): void {
                $query->whereHas(
                    'tags',
                    fn (Builder $query) => $query->where('tags.id', $tag ? $tag->id : 0)
                );
            });

        return $query;
    }

    public function scopeWithAnyTagsOfAnyType(Builder $query, $tags): Builder
    {
        $tags = static::convertToTagsOfAnyType($tags);

        $tagIds = collect($tags)->pluck('id');

        return $query->whereHas(
            'tags',
            fn (Builder $query) => $query->whereIn('tags.id', $tagIds)
        );
    }

    public function tagsWithType(?string $type = null): Collection
    {
        return $this->tags->filter(fn (Tag $tag) => $tag->type === $type);
    }

    /**
     * Attach tags with enhanced PHP 8.4 features
     */
    public function attachTags(array|ArrayAccess|Tag $tags, ?string $type = null): static
    {
        $tags = collect(Tag::findOrCreate($tags, $type));
        
        // Using array spread for better performance
        $tagIds = [...$tags->pluck('id')];
        $this->tags()->syncWithoutDetaching($tagIds);
        $this->clearTagsCache();

        return $this;
    }

    /**
     * @return \App\Models\Post|\App\Models\Product|\App\Models\Service
     */
    public function attachTag(string|Tag $tag, ?string $type = null)
    {
        return $this->attachTags([$tag], $type);
    }

    /**
     * @return $this
     */
    public function detachTags(array|ArrayAccess $tags, ?string $type = null): static
    {
        $tags = static::convertToTags($tags, $type);

        collect($tags)
            ->filter()
            ->each(fn (Tag $tag) => $this->tags()->detach($tag));

        return $this;
    }

    /**
     * @return $this
     */
    public function detachTag(string|Tag $tag, ?string $type = null): static
    {
        return $this->detachTags([$tag], $type);
    }

    /**
     * Sync tags with enhanced PHP 8.4 features
     */
    public function syncTags(string|array|ArrayAccess $tags): static
    {
        // Using match expression for cleaner type handling
        $tagsArray = match (true) {
            is_string($tags) => Arr::wrap($tags),
            default => $tags
        };

        $tags = collect(Tag::findOrCreate($tagsArray));
        
        // Using array spread for better performance
        $this->tags()->sync([...$tags->pluck('id')]);
        $this->clearTagsCache();

        return $this;
    }

    /**
     * @return $this
     */
    public function syncTagsWithType(array|ArrayAccess $tags, ?string $type = null): static
    {

        $tags = collect(Tag::findOrCreate($tags, $type));

        $this->syncTagIds($tags->pluck('id')->toArray(), $type);

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected static function convertToTags($values, $type = null)
    {
        if ($values instanceof Tag) {
            $values = [$values];
        }

        return collect($values)->map(function ($value) use ($type) {
            if ($value instanceof Tag) {
                if (isset($type) && $value->type !== $type) {
                    throw new InvalidArgumentException("Type was set to {$type} but tag is of type {$value->type}");
                }

                return $value;
            }

            return Tag::findFromString($value, $type);
        });
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    protected static function convertToTagsOfAnyType($values)
    {
        return collect($values)->map(function ($value) {
            if ($value instanceof Tag) {
                return $value;
            }

            return Tag::findFromStringOfAnyType($value);
        })->flatten();
    }

    protected function syncTagIds($ids, ?string $type = null, $detaching = true): void
    {
        $isUpdated = false;

        // Get a list of tag_ids for all current tags
        $current = $this->tags()
            ->newPivotStatement()
            ->where('taggable_id', $this->getKey())
            ->where('taggable_type', $this->getMorphClass())
            ->when($type !== null, function ($query) use ($type) {
                $tagModel = $this->tags()->getRelated();

                return $query->join(
                    $tagModel->getTable(),
                    'taggables.tag_id',
                    '=',
                    $tagModel->getTable().'.'.$tagModel->getKeyName()
                )
                    ->where($tagModel->getTable().'.type', $type);
            })
            ->pluck('tag_id')
            ->all();

        // Compare to the list of ids given to find the tags to remove
        $detach = array_diff($current, $ids);
        if ($detaching && count($detach) > 0) {
            $this->tags()->detach($detach);
            $isUpdated = true;
        }

        // Attach any new ids
        $attach = array_unique(array_diff($ids, $current));
        if (count($attach) > 0) {
            collect($attach)->each(function ($id): void {
                $this->tags()->attach($id, []);
            });
            $isUpdated = true;
        }

        // Once we have finished attaching or detaching the records, we will see if we
        // have done any attaching or detaching, and if we have we will touch these
        // relationships if they are configured to touch on any database updates.
        if ($isUpdated) {
            $this->tags()->touchIfTouching();
        }
    }

    /**
     * Check if model has specific tag with caching
     */
    public function hasTag($tag, ?string $type = null): bool
    {
        $cached = $this->_tagsCache ??= $this->tags->keyBy('name');
        
        return match (true) {
            $type !== null => $cached->where('type', $type)->contains(
                fn ($modelTag) => $modelTag->name === $tag || $modelTag->id === $tag
            ),
            default => $cached->contains(
                fn ($modelTag) => $modelTag->name === $tag || $modelTag->id === $tag
            )
        };
    }

    /**
     * Check if model has multiple tags
     * Using PHP 8.4 array spread for variadic parameters
     */
    public function hasTags(string ...$tags): bool
    {
        $cached = $this->_tagsCache ??= $this->tags->keyBy('name');
        
        return collect([...$tags])->every(
            fn (string $tag) => $cached->has($tag)
        );
    }

    /**
     * Clear tags cache
     */
    public function clearTagsCache(): void
    {
        $this->_tagsCache = null;
    }

    /**
     * Get tags count with type safety
     */
    public function getTagsCount(): int
    {
        return $this->tags()->count();
    }

    /**
     * Check if model has any tags
     */
    public function hasTagsAttached(): bool
    {
        return $this->getTagsCount() > 0;
    }

    /**
     * Get tags of specific type with caching
     */
    public function getTagsByType(string $type): Collection
    {
        return $this->tagsWithType($type);
    }

    /**
     * Get all tag names as array
     */
    public function getTagNames(): array
    {
        return $this->tags->pluck('name')->toArray();
    }

    /**
     * Get tags grouped by type
     */
    public function getTagsGroupedByType(): array
    {
        return $this->tags->groupBy('type')->map->pluck('name')->toArray();
    }
}
