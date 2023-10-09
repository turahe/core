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

namespace Modules\Core\Resource\Http;

use Modules\Core\Fields\FieldsCollection;

class CreateResourceRequest extends ResourcefulRequest
{
    /**
     * Get the fields for the current request.
     */
    public function fields(): FieldsCollection
    {
        return $this->resource()->resolveCreateFields();
    }
}
