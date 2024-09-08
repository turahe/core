<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

namespace Turahe\Core\Resource\Http;

use Illuminate\Database\Eloquent\Builder;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Models\Model;
use Turahe\Core\Resource\Resource;

trait InteractsWithResources
{
    /**
     * Custom resource id for the request.
     */
    protected null|int|string $customResourceId = null;

    /**
     * Custom resource for the request.
     */
    protected ?string $customResource = null;

    /**
     * Resource for the request.
     */
    protected ?Resource $resource = null;

    /**
     * The request resource record.
     */
    protected ?Model $record = null;

    /**
     * Get the resource name for the current request.
     */
    public function resourceName(): ?string
    {
        return $this->customResource ?: $this->route('resource');
    }

    /**
     * Set custom resource for the request.
     */
    public function setResource(string $name): static
    {
        $this->customResource = $name;

        $this->resource = null;
        $this->record = null;

        return $this;
    }

    /**
     * Get the request resource id.
     */
    public function resourceId(): int|string|null
    {
        return $this->customResourceId ?: $this->route('resourceId');
    }

    /**
     * Set custom resource id for the request.
     */
    public function setResourceId(int|string $id): static
    {
        $this->customResourceId = $id;

        $this->record = null;

        return $this;
    }

    /**
     * Get the class of the resource being requested.
     */
    public function resource(): Resource
    {
        if (! $this->resource) {
            $this->resource = tap(
                $this->findResource($this->resourceName()),
                function ($resource) {
                    abort_if(is_null($resource), 404);
                }
            );
        }

        return $this->resource;
    }

    /**
     * Get the resource record for the current request.
     */
    public function record(): Model
    {
        return $this->record ??= $this->newQuery()->findOrFail($this->resourceId());
    }

    /**
     * Manually set the record for the current update request.
     */
    public function setRecord(Model $record): static
    {
        $this->record = $record;

        return $this;
    }

    /**
     * Get new query from the current resource.
     */
    public function newQuery(): Builder
    {
        return $this->resource()->newQuery();
    }

    /**
     * Get resource by a given name.
     */
    public function findResource(?string $name): ?Resource
    {
        if (! $name) {
            return null;
        }

        return Innoclapps::resourceByName($name);
    }
}
