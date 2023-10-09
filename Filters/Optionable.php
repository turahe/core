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

namespace Modules\Core\Filters;

use Modules\Core\Fields\HasOptions;
use Modules\Core\Fields\ChangesKeys;

class Optionable extends Filter
{
    use ChangesKeys,
        HasOptions;
}
