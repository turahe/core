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
        $this->model = $model;

    }

    public function getTaxonomiesBuilder(string $order = 'created_at', string $sort = 'desc'): Builder
    {
        return $this->model->query()->orderBy($order, $sort);
    }

    public function getTaxonomies(string $order = 'created_at', string $sort = 'desc', $except = []): Collection
    {
        return $this->getTaxonomiesBuilder($order, $sort)->get()->except($except);
    }

    /**
     * @throws Exception
     */
    public function getTaxonomy(string $id): Taxonomy
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new ModelNotFoundException($exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getTaxonomyByName(string $name): Taxonomy
    {
        try {
            return $this->model->where('name', $name)->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function getTaxonomyBySlug(string $slug): Taxonomy
    {
        try {
            return $this->model->where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new ModelNotFoundException($exception->getMessage());
        }
    }

    /**
     * @throws Exception
     */
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

        try {
            return $this->model->create($data);
        } catch (QueryException $exception) {
            throw new Exception($exception->getMessage());
        }
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

    /**
     * @return mixed
     *
     * @throws Exception
     */
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

        try {
            return $this->model->update(array_filter($data));
        } catch (QueryException $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @return mixed
     *
     * @throws Exception
     */
    public function deleteTaxonomy(): bool
    {
        try {
            return $this->model->forceDelete();
        } catch (QueryException $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    public function trashTaxonomy(): bool
    {
        try {
            return $this->model->delete();
        } catch (QueryException $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
