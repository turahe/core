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

namespace Turahe\Core\Microsoft\Services\Batch;

class BatchDeleteRequest extends BatchRequest
{
    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod()
    {
        return 'DELETE';
    }
}
