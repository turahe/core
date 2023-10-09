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

namespace Modules\Core\Fields;

trait HasModelEvents
{
    /**
     * Handle the resource record "creating" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordCreating($model)
    {
    }

    /**
     * Handle the resource record "created" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordCreated($model)
    {
    }

    /**
     * Handle the resource record "updating" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordUpdating($model)
    {
    }

    /**
     * Handle the resource record "updated" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordUpdated($model)
    {
    }

    /**
     * Handle the resource record "deleting" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordDeleting($model)
    {
    }

    /**
     * Handle the resource record "deleted" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordDeleted($model)
    {
    }
}
