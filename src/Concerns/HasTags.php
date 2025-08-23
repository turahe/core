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

/**
 * HasTags Trait
 *
 * Provides comprehensive tagging functionality for Eloquent models.
 * This trait allows models to be tagged with multiple tags and provides
 * powerful querying capabilities for tag-based filtering and searching.
 *
 * Features:
 * - Many-to-many polymorphic relationship with tags
 * - Automatic tag synchronization and cleanup
 * - Advanced query scopes for tag filtering
 * - Tag type categorization
 * - Bulk tag operations
 * - Queue-based tag assignment for new models
 */
trait HasTags
{
    /**
     * Array to store tags that should be attached after model creation
     *
     * When tags are set on a model that doesn't exist yet, they are queued
     * and attached automatically after the model is created.
     */
    protected array $queuedTags = [];

    /**
     * Boot the HasTags trait
     *
     * Sets up model event listeners for automatic tag management:
     * - Attaches queued tags after model creation
     * - Detaches all tags when model is deleted
     */
    public static function bootHasTags(): void
    {
        static::created(function (Model $taggableModel): void {
            if (count($taggableModel->queuedTags) === 0) {
                return;
            }

            $taggableModel->attachTags($taggableModel->queuedTags);

            $taggableModel->queuedTags = [];
        });

        static::deleted(function (Model $deletedModel): void {
            $tags = $deletedModel->tags()->get();

            $deletedModel->detachTags($tags);
        });
    }

    /**
     * Get the morphToMany relationship with tags
     *
     * Returns a many-to-many polymorphic relationship with tags through
     * the taggables pivot table. Tags are automatically ordered.
     *
     * @return MorphToMany Relationship to tags with ordering
     */
    public function tags(): MorphToMany
    {
        return $this
            ->morphToMany(Tag::class, 'taggable', config('core.tables.taggables'))
            ->using(MorphPivot::class)
            ->ordered();
    }

    /**
     * Set tags attribute with automatic synchronization
     *
     * If the model doesn't exist yet, tags are queued for later attachment.
     * If the model exists, tags are immediately synchronized.
     *
     * @param  string|array|ArrayAccess|Tag  $tags  Tags to set
     */
    public function setTagsAttribute(string|array|ArrayAccess|Tag $tags): void
    {
        if (! $this->exists) {
            $this->queuedTags = $tags;

            return;
        }

        $this->syncTags($tags);
    }

    /**
     * Scope to include models that have ALL of the specified tags
     *
     * This scope ensures that models must have every single tag in the provided list.
     * Useful for finding models that match a complete set of criteria.
     *
     * @param  Builder  $query  The query builder instance
     * @param  string|array|ArrayAccess|Tag  $tags  Tags that must all be present
     * @param  string|null  $type  Optional tag type filter
     * @return Builder Modified query builder
     */
    public function scopeWithAllTags(Builder $query, string|array|ArrayAccess|Tag $tags, ?string $type = null): Builder
    {
        $tags = static::convertToTags($tags, $type);

        // Optimize by avoiding collect() and using direct iteration
        foreach ($tags as $tag) {
            $query->whereHas('tags', function (Builder $query) use ($tag): void {
                $query->where('tags.id', $tag->id ?? 0);
            });
        }

        return $query;
    }

    /**
     * Scope to include models that have ANY of the specified tags
     *
     * This scope finds models that have at least one of the provided tags.
     * Useful for broad searches where partial matches are acceptable.
     *
     * @param  Builder  $query  The query builder instance
     * @param  string|array|ArrayAccess|Tag  $tags  Tags to search for (any match)
     * @param  string|null  $type  Optional tag type filter
     * @return Builder Modified query builder
     */
    public function scopeWithAnyTags(Builder $query, string|array|ArrayAccess|Tag $tags, ?string $type = null): Builder
    {
        $tags = static::convertToTags($tags, $type);

        // Optimize by extracting IDs directly without collect()
        $tagIds = [];
        foreach ($tags as $tag) {
            $tagIds[] = $tag->id ?? 0;
        }

        return $query->whereHas('tags', function (Builder $query) use ($tagIds): void {
            $query->whereIn('tags.id', $tagIds);
        });
    }

    /**
     * Scope to exclude models that have ANY of the specified tags
     *
     * This scope finds models that do not have any of the provided tags.
     * Useful for filtering out models with unwanted tags.
     *
     * @param  Builder  $query  The query builder instance
     * @param  string|array|ArrayAccess|Tag  $tags  Tags to exclude
     * @param  string|null  $type  Optional tag type filter
     * @return Builder Modified query builder
     */
    public function scopeWithAllTagsOfAnyType(Builder $query, $tags): Builder
    {
        $tags = static::convertToTagsOfAnyType($tags);

        // Optimize by avoiding collect() and using direct iteration
        foreach ($tags as $tag) {
            $query->whereHas('tags', function (Builder $query) use ($tag): void {
                $query->where('tags.id', $tag ? $tag->id : 0);
            });
        }

        return $query;
    }

    public function scopeWithAnyTagsOfAnyType(Builder $query, $tags): Builder
    {
        $tags = static::convertToTagsOfAnyType($tags);

        // Optimize by extracting IDs directly without collect()
        $tagIds = [];
        foreach ($tags as $tag) {
            $tagIds[] = $tag ? $tag->id : 0;
        }

        return $query->whereHas('tags', function (Builder $query) use ($tagIds): void {
            $query->whereIn('tags.id', $tagIds);
        });
    }

    public function scopeWithoutTags(Builder $query, string|array|ArrayAccess|Tag $tags, ?string $type = null): Builder
    {
        $tags = static::convertToTags($tags, $type);

        // Optimize by extracting IDs directly without collect()
        $tagIds = [];
        foreach ($tags as $tag) {
            $tagIds[] = $tag->id ?? 0;
        }

        return $query->whereDoesntHave('tags', function (Builder $query) use ($tagIds): void {
            $query->whereIn('tags.id', $tagIds);
        });
    }

    public function tagsWithType(?string $type = null): Collection
    {
        return $this->tags->filter(fn (Tag $tag) => $tag->type === $type);
    }

    /**
     * @return $this
     */
    public function attachTags(array|ArrayAccess|Tag $tags, ?string $type = null): static
    {
        // Optimize by avoiding collect() and using direct array operations
        $tagModels = Tag::findOrCreate($tags, $type);
        $tagIds = [];

        foreach ($tagModels as $tag) {
            $tagIds[] = $tag->id;
        }

        $this->tags()->syncWithoutDetaching($tagIds);

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

        // Optimize by avoiding collect() and using direct iteration
        foreach ($tags as $tag) {
            if ($tag) {
                $this->tags()->detach($tag);
            }
        }

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
     * @return $this
     */
    public function syncTags(string|array|ArrayAccess $tags): static
    {
        if (is_string($tags)) {
            $tags = Arr::wrap($tags);
        }

        // Optimize by avoiding collect() and using direct array operations
        $tagModels = Tag::findOrCreate($tags);
        $tagIds = [];

        foreach ($tagModels as $tag) {
            $tagIds[] = $tag->id;
        }

        $this->tags()->sync($tagIds);

        return $this;
    }

    /**
     * Synchronize tags with type filtering
     *
     * @param  array|ArrayAccess  $tags  Tags to synchronize
     * @param  string|null  $type  Optional tag type filter
     * @return static Returns self for method chaining
     */
    public function syncTagsWithType(array|ArrayAccess $tags, ?string $type = null): static
    {
        // Optimize by avoiding collect() and using direct array operations
        $tagModels = Tag::findOrCreate($tags, $type);
        $tagIds = [];

        foreach ($tagModels as $tag) {
            $tagIds[] = $tag->id;
        }

        $this->syncTagIds($tagIds, $type);

        return $this;
    }

    /**
     * Convert various input types to Tag models
     *
     * @param  mixed  $values  Input values to convert
     * @param  string|null  $type  Optional tag type filter
     * @return array Array of Tag models
     *
     * @throws InvalidArgumentException When type mismatch occurs
     */
    protected static function convertToTags($values, $type = null): array
    {
        if ($values instanceof Tag) {
            $values = [$values];
        }

        // Optimize by avoiding collect() and using direct array operations
        $result = [];
        foreach ($values as $value) {
            if ($value instanceof Tag) {
                if (isset($type) && $value->type !== $type) {
                    throw new InvalidArgumentException("Type was set to {$type} but tag is of type {$value->type}");
                }
                $result[] = $value;
            } else {
                $result[] = Tag::findFromString($value, $type);
            }
        }

        return $result;
    }

    /**
     * Convert various input types to Tag models without type restrictions
     *
     * @param  mixed  $values  Input values to convert
     * @return array Array of Tag models
     */
    protected static function convertToTagsOfAnyType($values): array
    {
        // Optimize by avoiding collect() and using direct array operations
        $result = [];
        foreach ($values as $value) {
            if ($value instanceof Tag) {
                $result[] = $value;
            } else {
                $foundTags = Tag::findFromStringOfAnyType($value);
                if (is_array($foundTags)) {
                    $result = array_merge($result, $foundTags);
                } else {
                    $result[] = $foundTags;
                }
            }
        }

        return $result;
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
            // Optimize by avoiding collect() and using direct iteration
            foreach ($attach as $id) {
                $this->tags()->attach($id, []);
            }
            $isUpdated = true;
        }

        // Once we have finished attaching or detaching the records, we will see if we
        // have done any attaching or detaching, and if we have we will touch these
        // relationships if they are configured to touch on any database updates.
        if ($isUpdated) {
            $this->tags()->touchIfTouching();
        }
    }

    public function hasTag($tag, ?string $type = null): bool
    {
        return $this->tags
            ->when($type !== null, fn ($query) => $query->where('type', $type))
            ->contains(fn ($modelTag) => $modelTag->name === $tag || $modelTag->id === $tag);
    }
}
