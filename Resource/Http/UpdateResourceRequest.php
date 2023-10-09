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

class UpdateResourceRequest extends ResourcefulRequest
{
    /**
     * Get the fields for the current request.
     */
    public function fields(): FieldsCollection
    {
        $this->resource()->setModel($this->record());

        return $this->resource()->resolveUpdateFields();
    }
}
