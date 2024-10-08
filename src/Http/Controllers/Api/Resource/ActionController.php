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

namespace Turahe\Core\Http\Controllers\Api\Resource;

use Turahe\Core\Actions\ActionRequest;
use Turahe\Core\Http\Controllers\ApiController;

class ActionController extends ApiController
{
    /**
     * Run resource action.
     */
    public function handle($action, ActionRequest $request): mixed
    {
        $request->validateFields();

        return $request->run();
    }
}
