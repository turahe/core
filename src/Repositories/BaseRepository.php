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

abstract class BaseRepository implements BaseRepositoryInterface
{
    public $model;

    /**
     * @var BaseManager
     */
    public $manager;

    /**
     * @var BasePaginator
     */
    public $paginator;

    /**
     * BaseRepositoryTrait constructor.
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
     * @param  mixed  $paginator
     * @return array
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
     * Transform the Patient
     *
     * @return array
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
     * Transform Patient collection
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
     * @param  Model|Builder  $modelOrBuilder
     */
    public function queryBy($modelOrBuilder, array $params): Builder
    {
        if ($modelOrBuilder instanceof Model) {
            $query = $modelOrBuilder->newQuery();
        } else {
            $query = $modelOrBuilder; // Builder
        }

        if (! empty($params)) {
            foreach ($params as $key => $param) {
                if (! is_null($param)) {
                    $query->where($key, $param);
                }
            }
        }

        return $query;
    }

    /**
     * @param  Model|Builder  $model
     * @return mixed
     */
    public function getPaginatedModel($model, int $perPage = 25, string $orderBy = 'id', string $sortBy = 'asc')
    {
        return $model->orderBy($orderBy, $sortBy)->paginate($perPage);
    }
}
