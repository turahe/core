<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Turahe\Core\Contracts\TaxonomyRepositoryInterface;
use Turahe\Core\Models\Taxonomy;

class TaxonomyRepository extends BaseRepository implements TaxonomyRepositoryInterface
{
    public function __construct(Taxonomy $model)
    {
        parent::__construct($model);
    }

    public function getTaxonomiesBuilder(string $order = 'created_at', string $sort = 'desc'): Builder
    {
        return $this->model->query()->orderBy($order, $sort);
    }

    public function getTaxonomies(string $order = 'created_at', string $sort = 'desc', $except = []): Collection
    {
        return $this->getAll($order, $sort)->except($except);
    }

    public function getTaxonomy(string $id): Taxonomy
    {
        return $this->model->findOrFail($id);
    }

    public function getTaxonomyByName(string $name): Taxonomy
    {
        return $this->model->where('name', $name)->firstOrFail();
    }

    public function getTaxonomyBySlug(string $slug): Taxonomy
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }

    public function createTaxonomy(string $name, ?string $code = null, Taxonomy|string|int|null $parent = null, ?string $description = null): Taxonomy
    {
        if ($parent instanceof Taxonomy) {
            $parent = $parent->getKey();
        }
        $data = [
            'name' => $name,
            'code' => $code,
            'parent_id' => $parent,
            'description' => $description,
        ];

        return $this->model->create($data);
    }

    /**
     * Creates terms and taxonomies.
     *
     * @throws Exception
     */
    public function createTaxonomies(string|array $taxonomies, Taxonomy|string|int|null $parent = null): Collection
    {
        if (is_string($taxonomies)) {
            $taxonomies = explode('|', $taxonomies);
        }

        $terms = collect();

        if (count($taxonomies) > 0) {
            foreach ($taxonomies as $taxonomy) {
                $terms->push($this->createTaxonomy($taxonomy, mb_strtoupper($taxonomy), $parent));
            }
        }

        return $terms;
    }

    public function updateTaxonomy(string $name, ?string $code, Taxonomy|string|int|null $parent = null, $description = null): bool
    {
        if ($parent instanceof Taxonomy) {
            $parent = $parent->getKey();
        }
        $data = [
            'name' => $name,
            'code' => $code,
            'parent_id' => $parent,
            'description' => $description,
        ];

        return $this->model->update(array_filter($data));
    }

    public function deleteTaxonomy(): bool
    {
        return $this->model->forceDelete();
    }

    public function trashTaxonomy(): bool
    {
        return $this->model->delete();
    }
}
