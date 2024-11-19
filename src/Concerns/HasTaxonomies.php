<?php

declare(strict_types=1);

namespace Turahe\Core\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Turahe\Core\Contracts\TaxonomyRepositoryInterface;
use Turahe\Core\Models\Taxonomy;

trait HasTaxonomies
{
    /**
     * Boot the HasTaxonomies trait
     */
    protected static function bootHasTaxonomies(): void
    {
        static::deleting(function ($model): void {
            $model->detachTaxonomies();
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
            'model_has_taxonomies',
            'model_id',
            'taxonomy_id',

        )->withTimestamps();
    }

    /**
     * Add one or multiple terms (categories) within a given taxonomy.
     */
    public function addTaxonomies(string|array $categories, ?Taxonomy $parent = null): self
    {
        $taxonomies = app(TaxonomyRepositoryInterface::class)->createTaxonomies($categories, $parent);

        if (count($taxonomies) > 0) {
            foreach ($taxonomies as $taxonomy) {
                $this->taxonomies()->attach($taxonomy->getKey());
            }
        }

        return $this;
    }

    /**
     * Convenience method to add category to this model.
     */
    public function addTaxonomy(string|array $categories, ?Taxonomy $parent = null): self
    {
        return $this->addTaxonomies($categories, $parent);
    }

    /**
     * Get a term model by the given name and optionally a taxonomy.
     */
    public function getTaxonomy(string $term): ?Taxonomy
    {

        return $this->taxonomies->where('name', $term)->first();
    }

    /**
     * Check if this model belongs to a given category.
     */
    public function hasTaxonomy(string $term): bool
    {
        return (bool) $this->getTaxonomy($term);
    }

    /**
     * Detach all categories (related taxonomies via taxable table) from this model.
     */
    public function detachTaxonomies(): bool
    {
        return (bool) $this->taxonomies()->detach();
    }
}
