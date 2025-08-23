<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Turahe\Core\Contracts\TagRepositoryInterface;
use Turahe\Core\Models\Tag;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }

    public function getTags(string $order = 'created_at', string $sort = 'desc', $except = []): Collection
    {
        return $this->getAll($order, $sort)->except($except);
    }

    public function getTag(string $id): Tag
    {
        return $this->model->findOrFail($id);
    }

    public function getTagByName(string $name): Tag
    {
        return $this->model->where('name', $name)->firstOrFail();
    }

    public function getTagBySlug(string $slug): Tag
    {
        return $this->model->where('slug', $slug)->firstOrFail();
    }

    public function createTag(array $attributes): Tag
    {
        return $this->model->create($attributes);
    }

    public function updateTag(array $attributes): bool
    {
        return $this->model->update($attributes);
    }

    public function deleteTag(): bool
    {
        return $this->model->delete();
    }
}
