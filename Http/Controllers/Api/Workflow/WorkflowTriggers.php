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

namespace Modules\Core\Http\Controllers\Api\Workflow;

use Illuminate\Http\JsonResponse;
use Modules\Core\Workflow\Workflows;
use Modules\Core\Http\Controllers\ApiController;

class WorkflowTriggers extends ApiController
{
    /**
     * Get the available triggers.
     */
    public function __invoke(): JsonResponse
    {
        return $this->response(Workflows::triggersInstance());
    }
}
