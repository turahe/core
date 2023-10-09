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

namespace Modules\Core\Tests\Concerns;

use Modules\Core\Facades\Fields;

trait TestsCustomFields
{
    protected function fieldsTypesThatRequiresDatabaseColumnCreation()
    {
        return Fields::customFieldable()->where('multioptionable', false)->keys()->all();
    }

    protected function fieldsTypesThatDoesntRequiresDatabaseColumnCreation()
    {
        return Fields::customFieldable()->where('multioptionable', true)->keys()->all();
    }
}
