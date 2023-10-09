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

namespace Modules\Core\Contracts\Workflow;

interface ModelTrigger
{
    /**
     * The model class name the trigger is related to
     */
    public static function model(): string;
}
