<?php

declare(strict_types=1);

namespace Turahe\Core\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Turahe\Core\Models\Taxonomy;

interface TaxonomyRepositoryInterface
{
    public function getTaxonomiesBuilder(string $order = 'created_at', string $sort = 'desc'): Builder;

    public function getTaxonomies(string $order = 'created_at', string $sort = 'desc', $except = []): Collection;

    public function getTaxonomy(string $id): Taxonomy;

    public function getTaxonomyByName(string $name): Taxonomy;

    public function getTaxonomyBySlug(string $slug): Taxonomy;

    public function createTaxonomy(string $name, ?string $code = null, Taxonomy|string|int|null $parent = null, ?string $description = null): Taxonomy;

    public function createTaxonomies(string|array $taxonomies, Taxonomy|string|int|null $parent = null): Collection;

    public function updateTaxonomy(string $name, ?string $code, Taxonomy|string|int|null $parent = null, $description = null): bool;

    public function deleteTaxonomy(): bool;

    public function trashTaxonomy(): bool;
}
