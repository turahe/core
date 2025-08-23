<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

/**
 * Base Paginator Class
 * 
 * Handles pagination transformations using Fractal.
 * Optimized with better type safety and error handling.
 */
class BasePaginator
{
    /**
     * Transform a paginated collection using Fractal
     * 
     * @param LengthAwarePaginator $paginator The paginated collection
     * @param TransformerAbstract $transformer The transformer to use
     * @param string $resourceKey The resource key
     * @return Collection The transformed collection resource
     */
    public function paginate(LengthAwarePaginator $paginator, TransformerAbstract $transformer, string $resourceKey): Collection
    {
        try {
            $collection = $paginator->getCollection();
            $resource = new Collection($collection, $transformer, $resourceKey);
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

            return $resource;
        } catch (\Exception $e) {
            \Log::error('Pagination transformation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a simple collection resource without pagination
     * 
     * @param iterable $collection The collection to transform
     * @param TransformerAbstract $transformer The transformer to use
     * @param string $resourceKey The resource key
     * @return Collection The transformed collection resource
     */
    public function simpleCollection(iterable $collection, TransformerAbstract $transformer, string $resourceKey): Collection
    {
        return new Collection($collection, $transformer, $resourceKey);
    }
}
