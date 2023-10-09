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

trait Dateable
{
    /**
     * Resolve the field value for export.
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return string
     */
    public function resolveForExport($model)
    {
        return $model->{$this->attribute};
    }

    /**
     * Mark the field as clearable
     */
    public function clearable(): static
    {
        $this->withMeta(['attributes' => ['clearable' => true]]);

        return $this;
    }
}
