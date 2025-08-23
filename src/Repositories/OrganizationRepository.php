<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use Illuminate\Support\Collection;
use Turahe\Core\Contracts\OrganizationRepositoryInterface;
use Turahe\Core\Models\Organization;

class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    public function __construct(Organization $model)
    {
        parent::__construct($model);
    }

    public function getOrganizations(string $order = 'created_at', string $sort = 'desc', $except = []): Collection
    {
        return $this->getAll($order, $sort)->except($except);
    }

    public function getOrganization(string $id): Organization
    {
        return $this->model->findOrFail($id);
    }

    public function getOrganizationByName(string $name): Organization
    {
        return $this->model->where('name', $name)->firstOrFail();
    }

    public function getOrganizationBySlug(string $slug): Organization
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }

    public function createOrganization(array $attributes): Organization
    {
        return $this->model->create($attributes);
    }

    public function updateOrganization(array $attributes): bool
    {
        return $this->model->update($attributes);
    }

    public function deleteOrganization(): bool
    {
        return $this->model->forceDelete();
    }

    public function trashOrganization(): bool
    {
        return $this->model->delete();
    }
}
