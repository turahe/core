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

namespace Modules\Core\Resource;

use Modules\Core\Models\Model;
use Modules\Core\Contracts\Presentable;

class EmailSearch extends GlobalSearch
{
    /**
     * Provide the model data for the response.
     */
    protected function data(Model&Presentable $model, Resource $resource): array
    {
        return [
            'id'           => $model->getKey(),
            'address'      => $model->email,
            'name'         => $model->display_name,
            'path'         => $model->path,
            'resourceName' => $resource->name(),
        ];
    }
}
