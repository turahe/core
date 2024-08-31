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

namespace Turahe\Core\Actions;

use Illuminate\Support\Collection;

class SearchInGoogleAction extends Action
{
    /**
     * Indicates that this action is without confirmation dialog.
     */
    public bool $withoutConfirmation = true;

    /**
     * Indicates that the action will be hidden on the index view.
     */
    public bool $hideOnIndex = true;

    /**
     * Handle method.
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        return static::openInNewTab('https://www.google.com/search?q='.urlencode($models->first()->display_name));
    }

    /**
     * @param  \Illumindate\Database\Eloquent\Model  $model
     */
    public function authorizedToRun(ActionRequest $request, $model): bool
    {
        return $request->user()->can('view', $model);
    }

    /**
     * Action name.
     */
    public function name(): string
    {
        return __('core::actions.search_in_google');
    }
}
