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

namespace Modules\Core\Actions;

use Illuminate\Support\Collection;

class DeleteAction extends DestroyableAction
{
    /**
     * Indicates that the action will be hidden on the index view.
     */
    public bool $hideOnIndex = true;

    /**
     * Indicates whether the action bulk deletes models.
     */
    public bool $isBulk = false;

    /**
     * Authorized to run callback
     *
     * @var callable
     */
    protected $authorizedToRunWhen;

    /**
     * Model method action
     *
     * @var string
     */
    protected $method = 'delete';

    /**
     * Handle method.
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        foreach ($models as $model) {
            $model->{$this->method}();
        }
    }

    /**
     * Add authorization callback for the action.
     */
    public function authorizedToRunWhen(callable $callable): static
    {
        $this->authorizedToRunWhen = $callable;

        return $this;
    }

    /**
     * Determine if the action is executable for the given request.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        if (! $this->authorizedToRunWhen) {
            return $request->user()->can('delete', $model);
        }

        return call_user_func_array($this->authorizedToRunWhen, [$request, $model]);
    }

    /**
     * Set that the action will force delete the model.
     */
    public function forceDelete(): static
    {
        $this->method = 'forceDelete';

        return $this;
    }

    /**
     * Set that the action is bulk action.
     */
    public function isBulk(): static
    {
        $this->onlyOnIndex();
        $this->isBulk = true;

        return $this;
    }

    /**
     * Get the URI key for the card.
     */
    public function uriKey(): string
    {
        $key = $this->method === 'forceDelete' ? 'force-delete' : 'delete';

        return ($this->isBulk ? 'bulk-' : '').$key;
    }
}
