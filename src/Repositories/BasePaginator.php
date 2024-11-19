<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class BasePaginator
{
    public function paginate(LengthAwarePaginator $paginator, TransformerAbstract $transformer, $resourceKey): Collection
    {
        $collection = $paginator->getCollection();
        $resource = new Collection($collection, $transformer, $resourceKey);
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return $resource;
    }
}
