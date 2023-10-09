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

abstract class DestroyableAction extends Action
{
    /**
     * Handle method
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        foreach ($models as $model) {
            $model->delete();
        }
    }

    /**
     * Action name
     */
    public function name(): string
    {
        return __('core::app.delete');
    }
}
