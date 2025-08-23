<?php

declare(strict_types=1);

namespace Turahe\Core\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Turahe\Core\Contracts\TaxonomyRepositoryInterface;
use Turahe\Core\Models\Taxonomy;

/**
 * HasTaxonomies Trait
 * 
 * Provides taxonomy management functionality for Eloquent models.
 * This trait allows models to be categorized using hierarchical taxonomies,
 * similar to WordPress categories or Drupal taxonomies.
 * 
 * Features:
 * - Many-to-many polymorphic relationship with taxonomies
 * - Automatic taxonomy creation and management
 * - Hierarchical taxonomy support
 * - Bulk taxonomy operations
 * - Automatic cleanup on model deletion
 * 
 * @package Turahe\Core\Concerns
 */
trait HasTaxonomies
{
    /**
     * Boot the HasTaxonomies trait
     * 
     * Sets up model event listeners for automatic taxonomy management:
     * - Detaches all taxonomies when the model is deleted
     */
    protected static function bootHasTaxonomies(): void
    {
        static::deleting(function ($model): void {
            $model->detachTaxonomies();
        });
    }

    /**
     * Return a collection of taxonomies for this model.
     * 
     * Returns a many-to-many polymorphic relationship with taxonomies through
     * the model_has_taxonomies pivot table with timestamps.
     * 
     * @return MorphToMany Relationship to taxonomies with timestamp data
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
     * 
     * Creates new taxonomy terms if they don't exist and attaches them to the model.
     * Optionally supports hierarchical taxonomies by specifying a parent taxonomy.
     * 
     * @param string|array $categories Category names to add
     * @param Taxonomy|null $parent Optional parent taxonomy for hierarchical structure
     * @return self Returns the model instance for method chaining
     */
    public function addTaxonomies(string|array $categories, ?Taxonomy $parent = null): self
    {
        $taxonomies = app(TaxonomyRepositoryInterface::class)->createTaxonomies($categories, $parent);

        if (count($taxonomies) > 0) {
            // Optimize by collecting all IDs first and then attaching in bulk
            $taxonomyIds = [];
            foreach ($taxonomies as $taxonomy) {
                $taxonomyIds[] = $taxonomy->getKey();
            }
            
            if (!empty($taxonomyIds)) {
                $this->taxonomies()->attach($taxonomyIds);
            }
        }

        return $this;
    }

    /**
     * Convenience method to add category to this model.
     * 
     * This is an alias for addTaxonomies() for better readability when adding
     * single categories.
     * 
     * @param string|array $categories Category names to add
     * @param Taxonomy|null $parent Optional parent taxonomy for hierarchical structure
     * @return self Returns the model instance for method chaining
     */
    public function addTaxonomy(string|array $categories, ?Taxonomy $parent = null): self
    {
        return $this->addTaxonomies($categories, $parent);
    }

    /**
     * Get a taxonomy term by name from the model's attached taxonomies.
     * 
     * Searches through the model's current taxonomies to find a term
     * with the specified name.
     * 
     * @param string $term The taxonomy term name to search for
     * @return Taxonomy|null The found taxonomy or null if not found
     */
    public function getTaxonomy(string $term): ?Taxonomy
    {

        return $this->taxonomies->where('name', $term)->first();
    }

    /**
     * Check if this model belongs to a given category.
     * 
     * Determines whether the model has a taxonomy term with the specified name.
     * 
     * @param string $term The taxonomy term name to check
     * @return bool True if the model has the specified taxonomy term
     */
    public function hasTaxonomy(string $term): bool
    {
        return (bool) $this->getTaxonomy($term);
    }

    /**
     * Detach all categories (related taxonomies via taxable table) from this model.
     * 
     * Removes all taxonomy relationships from the model without deleting
     * the taxonomy terms themselves.
     * 
     * @return bool True if the operation was successful
     */
    public function detachTaxonomies(): bool
    {
        return (bool) $this->taxonomies()->detach();
    }
}
