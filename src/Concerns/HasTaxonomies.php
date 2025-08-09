<?php

declare(strict_types=1);

namespace Turahe\Core\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Turahe\Core\Contracts\TaxonomyRepositoryInterface;
use Turahe\Core\Models\Taxonomy;

trait HasTaxonomies
{
    /**
     * Cached taxonomies for performance optimization
     */
    private ?Collection $_taxonomiesCache = null;

    /**
     * Boot the HasTaxonomies trait
     */
    protected static function bootHasTaxonomies(): void
    {
        static::deleting(function ($model): void {
            $model->detachTaxonomies();
            $model->clearTaxonomiesCache();
        });

        static::saved(function ($model): void {
            $model->clearTaxonomiesCache();
        });
    }

    /**
     * Return a collection of taxonomies for this model.
     */
    public function taxonomies(): MorphToMany
    {
        return $this->morphToMany(
            Taxonomy::class,
            'model',
            config('core.tables.model_has_taxonomies'),
            'model_id',
            'taxonomy_id',
        )->withTimestamps();
    }

    /**
     * Add one or multiple terms (categories) within a given taxonomy.
     * Enhanced with PHP 8.4 features for better performance
     */
    public function addTaxonomies(string|array $categories, ?Taxonomy $parent = null): static
    {
        $taxonomies = app(TaxonomyRepositoryInterface::class)->createTaxonomies($categories, $parent);

        // Using PHP 8.4 array spread for better performance
        if (!empty($taxonomies)) {
            $taxonomyIds = array_map(fn (Taxonomy $taxonomy) => $taxonomy->getKey(), [...$taxonomies]);
            $this->taxonomies()->syncWithoutDetaching($taxonomyIds);
            $this->clearTaxonomiesCache();
        }

        return $this;
    }

    /**
     * Convenience method to add category to this model.
     */
    public function addTaxonomy(string|array $categories, ?Taxonomy $parent = null): static
    {
        return $this->addTaxonomies($categories, $parent);
    }

    /**
     * Get a term model by the given name and optionally a taxonomy.
     * Using property hooks for caching
     */
    public function getTaxonomy(string $term): ?Taxonomy
    {
        $cached = $this->_taxonomiesCache ??= $this->taxonomies->keyBy('name');
        
        return $cached->get($term);
    }

    /**
     * Check if this model belongs to a given category.
     * Using match expression for cleaner logic
     */
    public function hasTaxonomy(string $term): bool
    {
        return match (true) {
            $this->getTaxonomy($term) !== null => true,
            default => false
        };
    }

    /**
     * Check if model has multiple taxonomies
     * Using PHP 8.4 array spread for variadic parameters
     */
    public function hasTaxonomies(string ...$terms): bool
    {
        $cached = $this->_taxonomiesCache ??= $this->taxonomies->keyBy('name');
        
        return collect([...$terms])->every(
            fn (string $term) => $cached->has($term)
        );
    }

    /**
     * Get taxonomies by type with caching
     */
    public function getTaxonomiesByType(string $type): Collection
    {
        return $this->taxonomies->where('type', $type);
    }

    /**
     * Sync taxonomies with better performance
     */
    public function syncTaxonomies(array $taxonomyIds): static
    {
        $this->taxonomies()->sync($taxonomyIds);
        $this->clearTaxonomiesCache();
        
        return $this;
    }

    /**
     * Clear taxonomies cache
     */
    public function clearTaxonomiesCache(): void
    {
        $this->_taxonomiesCache = null;
    }

    /**
     * Detach all categories (related taxonomies via taxable table) from this model.
     */
    public function detachTaxonomies(): bool
    {
        $result = (bool) $this->taxonomies()->detach();
        $this->clearTaxonomiesCache();
        
        return $result;
    }

    /**
     * Get taxonomies count with type safety
     */
    public function getTaxonomiesCount(): int
    {
        return $this->taxonomies()->count();
    }

    /**
     * Check if model has any taxonomies
     */
    public function hasTaxonomiesAttached(): bool
    {
        return $this->getTaxonomiesCount() > 0;
    }
}
