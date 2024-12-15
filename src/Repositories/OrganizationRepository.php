<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Turahe\Core\Contracts\OrganizationRepositoryInterface;
use Turahe\Core\Models\Organization;

class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    public function __construct(Organization $model)
    {
        parent::__construct($model);
        $this->model = $model;

    }

    public function getOrganizations(string $order = 'created_at', string $sort = 'desc', $except = []): Collection
    {
        return $this->model->orderBy($order, $sort)->get()->except($except);
    }

    public function getOrganization(string $id): Organization
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new ModelNotFoundException($exception->getMessage());
        }
    }

    public function getOrganizationByName(string $name): Organization
    {
        try {
            return $this->model->where('name', $name)->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new ModelNotFoundException($exception->getMessage());
        }
    }

    public function getOrganizationBySlug(string $slug): Organization
    {
        try {
            return $this->model->where('slug', $slug)->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new ModelNotFoundException($exception->getMessage());
        }
    }

    public function createOrganization(array $attributes): Organization
    {
        try {
            return $this->model->create($attributes);
        } catch (ModelNotFoundException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function updateOrganization(array $attributes): bool
    {
        try {
            return $this->model->update($attributes);
        } catch (QueryException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function deleteOrganization(): bool
    {
        try {
            return $this->model->forceDelete();
        } catch (QueryException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function trashOrganization(): bool
    {
        try {
            return $this->model->delete();
        } catch (QueryException $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}
