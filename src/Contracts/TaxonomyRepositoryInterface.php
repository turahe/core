<?php

declare(strict_types=1);

namespace Turahe\Core\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Turahe\Core\Models\Taxonomy;

/**
 * Taxonomy Repository Interface
 *
 * Defines the contract for taxonomy management operations including CRUD operations,
 * hierarchical structure management, and bulk operations for taxonomy terms.
 *
 * This interface provides methods for:
 * - Retrieving taxonomies with various filtering and ordering options
 * - Creating and updating taxonomy terms
 * - Managing hierarchical relationships between taxonomies
 * - Bulk taxonomy operations
 * - Soft delete and restore functionality
 */
interface TaxonomyRepositoryInterface
{
    /**
     * Get a query builder for taxonomies with specified ordering
     *
     * @param  string  $order  The column to order by (default: 'created_at')
     * @param  string  $sort  The sort direction (default: 'desc')
     * @return Builder Query builder instance for further customization
     */
    public function getTaxonomiesBuilder(string $order = 'created_at', string $sort = 'desc'): Builder;

    /**
     * Get a collection of taxonomies with specified ordering and exclusions
     *
     * @param  string  $order  The column to order by (default: 'created_at')
     * @param  string  $sort  The sort direction (default: 'desc')
     * @param  array  $except  Array of taxonomy IDs to exclude from results
     * @return Collection Collection of taxonomy models
     */
    public function getTaxonomies(string $order = 'created_at', string $sort = 'desc', $except = []): Collection;

    /**
     * Get a taxonomy by its ID
     *
     * @param  string  $id  The taxonomy ID
     * @return Taxonomy The found taxonomy model
     */
    public function getTaxonomy(string $id): Taxonomy;

    /**
     * Get a taxonomy by its name
     *
     * @param  string  $name  The taxonomy name
     * @return Taxonomy The found taxonomy model
     */
    public function getTaxonomyByName(string $name): Taxonomy;

    /**
     * Get a taxonomy by its slug
     *
     * @param  string  $slug  The taxonomy slug
     * @return Taxonomy The found taxonomy model
     */
    public function getTaxonomyBySlug(string $slug): Taxonomy;

    /**
     * Create a new taxonomy term
     *
     * @param  string  $name  The taxonomy name
     * @param  string|null  $code  Optional taxonomy code
     * @param  Taxonomy|string|int|null  $parent  Optional parent taxonomy
     * @param  string|null  $description  Optional taxonomy description
     * @return Taxonomy The created taxonomy model
     */
    public function createTaxonomy(string $name, ?string $code = null, Taxonomy|string|int|null $parent = null, ?string $description = null): Taxonomy;

    /**
     * Create multiple taxonomy terms
     *
     * @param  string|array  $taxonomies  Array of taxonomy names or single name
     * @param  Taxonomy|string|int|null  $parent  Optional parent taxonomy for all created terms
     * @return Collection Collection of created taxonomy models
     */
    public function createTaxonomies(string|array $taxonomies, Taxonomy|string|int|null $parent = null): Collection;

    /**
     * Update an existing taxonomy term
     *
     * @param  string  $name  The new taxonomy name
     * @param  string|null  $code  The new taxonomy code
     * @param  Taxonomy|string|int|null  $parent  The new parent taxonomy
     * @param  string|null  $description  The new taxonomy description
     * @return bool True if update was successful
     */
    public function updateTaxonomy(string $name, ?string $code, Taxonomy|string|int|null $parent = null, $description = null): bool;

    /**
     * Permanently delete a taxonomy term
     *
     * @return bool True if deletion was successful
     */
    public function deleteTaxonomy(): bool;

    /**
     * Soft delete a taxonomy term (move to trash)
     *
     * @return bool True if soft deletion was successful
     */
    public function trashTaxonomy(): bool;
}
