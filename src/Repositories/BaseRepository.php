<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Turahe\Core\Contracts\BaseRepositoryInterface;

/**
 * Base Repository Class
 * 
 * Abstract base class for all repository implementations in the Turahe Core package.
 * Provides common functionality for data transformation, pagination, and API response
 * building using the Fractal transformation library.
 * 
 * Features:
 * - Model transformation using Fractal transformers
 * - Pagination support with transformation
 * - Collection transformation
 * - API versioning support
 * - Include/exclude functionality for related data
 * 
 * @package Turahe\Core\Repositories
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * The Eloquent model instance this repository works with
     * 
     * @var Model
     */
    public $model;

    /**
     * Manager instance for building API responses
     * 
     * @var BaseManager
     */
    public $manager;

    /**
     * Paginator instance for handling pagination transformations
     * 
     * @var BasePaginator
     */
    public $paginator;

    /**
     * BaseRepository constructor
     * 
     * Initializes the repository with a model instance and creates
     * the necessary manager and paginator instances for data transformation.
     * 
     * @param Model $model The Eloquent model instance
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->manager = new BaseManager;
        $this->paginator = new BasePaginator;
    }

    /**
     * @return array
     *
     * @deprecated Use @transformPaginatedModel to prevent confusion on Model paginate method
     */
    //    public function paginate(
    //        LengthAwarePaginator $paginator,
    //        TransformerAbstract $transformer,
    //        $resourceKey,
    //        array $includes = [],
    //        ?string $apiVer = null
    //    ) {
    //        $resource = $this->paginator->paginate($paginator, $transformer, $resourceKey);
    //
    //        return $this->manager->buildData($resource, $includes, $apiVer);
    //    }

    /**
     * Transform a paginated model collection using Fractal
     * 
     * Takes a Laravel paginator instance and transforms it using the specified
     * transformer, returning a structured API response with pagination metadata.
     * 
     * @param LengthAwarePaginator $paginator The paginated model collection
     * @param TransformerAbstract $transformer The Fractal transformer to use
     * @param string $resourceKey The key for the transformed resource
     * @param array $includes Array of related data to include
     * @param string|null $apiVer API version for the response
     * @return array Transformed paginated data with metadata
     */
    public function transformPaginatedModel(
        LengthAwarePaginator $paginator,
        TransformerAbstract $transformer,
        $resourceKey,
        array $includes = [],
        ?string $apiVer = null
    ) {
        $resource = $this->paginator->paginate($paginator, $transformer, $resourceKey);

        return $this->manager->buildData($resource, $includes, $apiVer);
    }

    /**
     * Transform a single model instance using Fractal
     * 
     * Takes a single Eloquent model and transforms it using the specified
     * transformer, returning a structured API response.
     * 
     * @param Model $model The Eloquent model to transform
     * @param TransformerAbstract $transformer The Fractal transformer to use
     * @param string $resourceKey The key for the transformed resource
     * @param array $includes Array of related data to include
     * @param string|null $apiVer API version for the response
     * @return array Transformed model data
     */
    public function transformItem(
        Model $model,
        TransformerAbstract $transformer,
        $resourceKey,
        array $includes = [],
        ?string $apiVer = null
    ) {
        $resource = new Item($model, $transformer, $resourceKey);

        return $this->manager->buildData($resource, $includes, $apiVer);
    }

    /**
     * Transform a collection of models using Fractal
     * 
     * Takes a collection of Eloquent models and transforms them using the specified
     * transformer, returning a structured API response.
     * 
     * @param Collection $collection The collection of models to transform
     * @param TransformerAbstract $transformer The Fractal transformer to use
     * @param string $resourceKey The key for the transformed resource
     * @param array $includes Array of related data to include
     * @param string|null $apiVer API version for the response
     * @return array Transformed collection data
     */
    public function transformCollection(
        $collection,
        TransformerAbstract $transformer,
        $resourceKey,
        array $includes = [],
        ?string $apiVer = null
    ): array {
        $resource = new Collection($collection, $transformer, $resourceKey);

        return $this->manager->buildData($resource, $includes, $apiVer);
    }

    /**
     * Build a query based on model or builder and parameters
     * 
     * @param Model|Builder $modelOrBuilder The model instance or query builder
     * @param array $params Array of parameters to apply as where clauses
     * @return Builder The configured query builder
     */
    public function queryBy($modelOrBuilder, array $params): Builder
    {
        $query = $modelOrBuilder instanceof Model 
            ? $modelOrBuilder->newQuery() 
            : $modelOrBuilder;

        // Optimize by filtering out null values first
        $validParams = array_filter($params, fn($param) => $param !== null);
        
        if (!empty($validParams)) {
            $query->where($validParams);
        }

        return $query;
    }

    /**
     * Get a paginated model collection with ordering
     * 
     * @param Model|Builder $model The model instance or query builder
     * @param int $perPage Number of items per page
     * @param string $orderBy Column to order by
     * @param string $sortBy Sort direction (asc/desc)
     * @return LengthAwarePaginator The paginated collection
     */
    public function getPaginatedModel($model, int $perPage = 25, string $orderBy = 'id', string $sortBy = 'asc'): LengthAwarePaginator
    {
        $query = $model instanceof Model ? $model->newQuery() : $model;
        
        return $query->orderBy($orderBy, $sortBy)->paginate($perPage);
    }
}
