<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
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
 * - Caching support for performance optimization
 * - Query building optimization
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * The Eloquent model instance this repository works with
     */
    protected Model $model;

    /**
     * Manager instance for building API responses
     */
    protected BaseManager $manager;

    /**
     * Paginator instance for handling pagination transformations
     */
    protected BasePaginator $paginator;

    /**
     * Cache TTL in seconds for repository methods
     */
    protected int $cacheTtl = 3600; // 1 hour default

    /**
     * BaseRepository constructor
     *
     * Initializes the repository with a model instance and creates
     * the necessary manager and paginator instances for data transformation.
     *
     * @param  Model  $model  The Eloquent model instance
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->manager = new BaseManager;
        $this->paginator = new BasePaginator;
    }

    /**
     * Get the model instance
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set cache TTL for this repository
     *
     * @param  int  $ttl  Cache TTL in seconds
     */
    public function setCacheTtl(int $ttl): self
    {
        $this->cacheTtl = $ttl;

        return $this;
    }

    /**
     * Get cache TTL for this repository
     */
    public function getCacheTtl(): int
    {
        return $this->cacheTtl;
    }

    /**
     * Generate cache key for repository methods
     *
     * @param  string  $method  Method name
     * @param  array  $params  Method parameters
     */
    protected function generateCacheKey(string $method, array $params = []): string
    {
        $paramsHash = md5(serialize($params));

        return sprintf('%s:%s:%s:%s',
            get_class($this->model),
            $method,
            $paramsHash,
            $this->cacheTtl
        );
    }

    /**
     * Transform a paginated model collection using Fractal
     *
     * Takes a Laravel paginator instance and transforms it using the specified
     * transformer, returning a structured API response with pagination metadata.
     *
     * @param  LengthAwarePaginator  $paginator  The paginated model collection
     * @param  TransformerAbstract  $transformer  The Fractal transformer to use
     * @param  string  $resourceKey  The key for the transformed resource
     * @param  array  $includes  Array of related data to include
     * @param  string|null  $apiVer  API version for the response
     * @return array Transformed paginated data with metadata
     */
    public function transformPaginatedModel(
        LengthAwarePaginator $paginator,
        TransformerAbstract $transformer,
        string $resourceKey,
        array $includes = [],
        ?string $apiVer = null
    ): array {
        $resource = $this->paginator->paginate($paginator, $transformer, $resourceKey);

        return $this->manager->buildData($resource, $includes, $apiVer);
    }

    /**
     * Transform a single model instance using Fractal
     *
     * Takes a single Eloquent model and transforms it using the specified
     * transformer, returning a structured API response.
     *
     * @param  Model  $model  The Eloquent model to transform
     * @param  TransformerAbstract  $transformer  The Fractal transformer to use
     * @param  string  $resourceKey  The key for the transformed resource
     * @param  array  $includes  Array of related data to include
     * @param  string|null  $apiVer  API version for the response
     * @return array Transformed model data
     */
    public function transformItem(
        Model $model,
        TransformerAbstract $transformer,
        string $resourceKey,
        array $includes = [],
        ?string $apiVer = null
    ): array {
        $resource = new Item($model, $transformer, $resourceKey);

        return $this->manager->buildData($resource, $includes, $apiVer);
    }

    /**
     * Transform a collection of models using Fractal
     *
     * Takes a collection of Eloquent models and transforms them using the specified
     * transformer, returning a structured API response.
     *
     * @param  iterable  $collection  The collection of models to transform
     * @param  TransformerAbstract  $transformer  The Fractal transformer to use
     * @param  string  $resourceKey  The key for the transformed resource
     * @param  array  $includes  Array of related data to include
     * @param  string|null  $apiVer  API version for the response
     * @return array Transformed collection data
     */
    public function transformCollection(
        iterable $collection,
        TransformerAbstract $transformer,
        string $resourceKey,
        array $includes = [],
        ?string $apiVer = null
    ): array {
        $resource = new Collection($collection, $transformer, $resourceKey);

        return $this->manager->buildData($resource, $includes, $apiVer);
    }

    /**
     * Build a query based on model or builder and parameters
     *
     * @param  Model|Builder  $modelOrBuilder  The model instance or query builder
     * @param  array  $params  Array of parameters to apply as where clauses
     * @return Builder The configured query builder
     */
    public function queryBy($modelOrBuilder, array $params): Builder
    {
        $query = $modelOrBuilder instanceof Model
            ? $modelOrBuilder->newQuery()
            : $modelOrBuilder;

        // Optimize by filtering out null values and empty strings first
        $validParams = array_filter($params, function ($value) {
            return $value !== null && $value !== '' && $value !== [];
        });

        if (! empty($validParams)) {
            $query->where($validParams);
        }

        return $query;
    }

    /**
     * Get a paginated model collection with ordering
     *
     * @param  Model|Builder  $model  The model instance or query builder
     * @param  int  $perPage  Number of items per page
     * @param  string  $orderBy  Column to order by
     * @param  string  $sortBy  Sort direction (asc/desc)
     * @return LengthAwarePaginator The paginated collection
     */
    public function getPaginatedModel($model, int $perPage = 25, string $orderBy = 'id', string $sortBy = 'asc'): LengthAwarePaginator
    {
        $query = $model instanceof Model ? $model->newQuery() : $model;

        return $query->orderBy($orderBy, $sortBy)->paginate($perPage);
    }

    /**
     * Get all records with optional caching
     *
     * @param  string  $orderBy  Column to order by
     * @param  string  $sortBy  Sort direction
     * @param  bool  $useCache  Whether to use caching
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(string $orderBy = 'id', string $sortBy = 'asc', bool $useCache = false)
    {
        if ($useCache) {
            $cacheKey = $this->generateCacheKey('getAll', [$orderBy, $sortBy]);

            return Cache::remember($cacheKey, $this->cacheTtl, function () use ($orderBy, $sortBy) {
                return $this->model->orderBy($orderBy, $sortBy)->get();
            });
        }

        return $this->model->orderBy($orderBy, $sortBy)->get();
    }

    /**
     * Find a record by ID with optional caching
     *
     * @param  int|string  $id  Record ID
     * @param  bool  $useCache  Whether to use caching
     * @return Model|null
     */
    public function findById($id, bool $useCache = false)
    {
        if ($useCache) {
            $cacheKey = $this->generateCacheKey('findById', [$id]);

            return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
                return $this->model->find($id);
            });
        }

        return $this->model->find($id);
    }

    /**
     * Clear cache for this repository
     */
    public function clearCache(): bool
    {
        $pattern = sprintf('%s:*', get_class($this->model));

        return Cache::flush();
    }
}
