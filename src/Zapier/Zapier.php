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

namespace Turahe\Core\Zapier;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Turahe\Core\Models\ZapierHook;
use Turahe\Core\Resource\Resource;
use Illuminate\Database\Eloquent\Collection;
use Turahe\Core\Resource\Http\ResourceRequest;

class Zapier
{
    /**
     * Resource cached hooks.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $resourceHooks;

    /**
     * Qeued data for hook request.
     */
    protected static array $queue = [];

    /**
     * Supported actions.
     */
    protected static array $supportedActions = ['created', 'updated'];

    /**
     * Add records to for the given action to the hooks queue
     */
    public function queue(string $action, array|int $records, Resource $resource): static
    {
        $this->validateActionName($action);

        if (! isset(static::$queue[$resource->name()])) {
            static::$queue[$resource->name()] = [];
        }

        if (! isset(static::$queue[$resource->name()][$action])) {
            static::$queue[$resource->name()][$action] = [];
        }

        static::$queue[$resource->name()][$action] = array_merge(
            static::$queue[$resource->name()][$action],
            Arr::wrap($records)
        );

        static::$queue[$resource->name()]['_resource'] = $resource;

        return $this;
    }

    /**
     * Process the queued action models
     */
    public function processQueue(): void
    {
        foreach (static::$queue as $resourceName => $actions) {
            $resource = Arr::pull($actions, '_resource');

            foreach ($actions as $actionName => $modelIds) {
                $modelIds = array_unique($modelIds);

                if (count($modelIds) > 0 && $this->getResourceActionZaps($resource->name(), $actionName)->isNotEmpty()) {
                    $models = $resource->displayQuery()
                        ->whereIn(
                            $resource->newModel()->getKeyName(),
                            $modelIds
                        )->get();

                    $this->dispatchZaps($actionName, $resource, $models);
                }
            }
        }

        static::$queue = [];
    }

    /**
     * Dispatch the given action Zaps for processing for the given resource
     *
     * @param  \Illuminate\Support\Collection  $models
     * @return void
     */
    protected function dispatchZaps(string $actionName, Resource $resource, $models)
    {
        // Temporarily change the header for Zapier as the JsonResource class
        // is checking whether the request is for Zapier
        // Not needed to be an instance of ResourceRequest but for consistency, use the ResourceRequest class
        $request = app(ResourceRequest::class)->setResource($resource->name());
        $originalUserAgent = $request->headers->get('user-agent');
        $request->headers->set('user-agent', 'Zapier');

        foreach ($this->getResourceActionZaps($resource->name(), $actionName) as $zap) {
            ProcessZapHookAction::dispatch($zap->hook, json_decode(
                json_encode(
                    $resource->createJsonResource(
                        $this->filterModelsForDispatch($models, $zap, $actionName),
                        true,
                        $request
                    )
                )
            ));
        }

        // Reset the user-agent header
        $request->headers->set('user-agent', $originalUserAgent);
    }

    /**
     * Filter the models for dispatch
     *
     * @param  \Illuminate\Support\Collection  $models
     * @param  \Turahe\Core\Models\ZapierHook  $zap
     * @param  string  $actionName
     * @return \Illuminate\Support\Collection
     */
    protected function filterModelsForDispatch($models, $zap, $actionName)
    {
        return $models->filter(function ($model) use ($zap, $actionName) {
            if (isset($zap->data['triggerWhen'])) {
                // Filter empty values
                $triggerWhen = array_filter($zap->data['triggerWhen']);

                // Get the attributes keys only
                $attributes = array_keys($triggerWhen);

                switch ($actionName) {
                    case 'updated':
                        // Attributes not updated, exclude the model
                        if (! $model->isDirty($attributes)) {
                            return false;
                        }

                        // The attributes are not equal like the selected
                        // In this case, we exclude the model from the payload
                        if ($model->only($attributes) != $triggerWhen) {
                            return false;
                        }

                        break;

                    case 'created':
                        // Model attributes does not match the given attributes
                        if ($model->only($attributes) != $triggerWhen) {
                            return false;
                        }

                        break;
                }
            }

            return $zap->user->can('view', $model);
        })->values();
    }

    /**
     * Validate the given action name
     *
     * @throws \Turahe\Core\Zapier\ActionNotSupportedException
     */
    public function validateActionName(string $name): void
    {
        throw_unless(
            in_array($name, static::$supportedActions),
            new ActionNotSupportedException($name)
        );
    }

    /**
     * Get the supported actions
     */
    public function supportedActions(): array
    {
        return static::$supportedActions;
    }

    /**
     * Get the given resource registered hooks.
     */
    public function getResourceHooks(string $name): Collection
    {
        if (! isset($this->resourceHooks[$name])) {
            $this->resourceHooks[$name] = ZapierHook::with('user')->byResource($name)->get();
        }

        return $this->resourceHooks[$name];
    }

    /**
     * Get the given action Zaps for the given resource.
     */
    public function getResourceActionZaps(string $resourceName, string $actionName): Collection
    {
        return $this->getResourceHooks($resourceName)->where('action', $actionName);
    }
}
