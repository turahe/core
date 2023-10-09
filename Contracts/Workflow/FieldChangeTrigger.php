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

interface FieldChangeTrigger
{
    /**
     * The field to track changes on
     */
    public static function field(): string;

    /**
     * Provide the change field
     *
     * @return \Modules\Core\Fields\Field
     */
    public static function changeField();
}
