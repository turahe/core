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

namespace Turahe\Core\Http\Controllers\Api\Workflow;

use Illuminate\Http\JsonResponse;
use Turahe\Core\Workflow\Workflows;
use Turahe\Core\Http\Controllers\ApiController;

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
