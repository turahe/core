<?php

declare(strict_types=1);

namespace Turahe\Core\Contracts;

use Illuminate\Support\Collection;
use Turahe\Core\Models\Tag;

interface TagRepositoryInterface extends BaseRepositoryInterface
{
    public function getTags(string $order = 'created_at', string $sort = 'desc', $except = []): Collection;

    public function getTag(string $id): Tag;

    public function getTagByName(string $name): Tag;

    public function getTagBySlug(string $slug): Tag;

    public function createTag(array $attributes): Tag;

    public function updateTag(array $attributes): bool;

    public function deleteTag(): bool;
}
