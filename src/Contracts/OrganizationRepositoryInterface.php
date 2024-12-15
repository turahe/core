<?php

declare(strict_types=1);

namespace Turahe\Core\Contracts;

use Illuminate\Support\Collection;
use Turahe\Core\Models\Organization;

interface OrganizationRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrganizations(string $order = 'created_at', string $sort = 'desc', $except = []): Collection;

    public function getOrganization(string $id): Organization;

    public function getOrganizationByName(string $name): Organization;

    public function getOrganizationBySlug(string $slug): Organization;

    public function createOrganization(array $attributes): Organization;

    public function updateOrganization(array $attributes): bool;

    public function deleteOrganization(): bool;
}
