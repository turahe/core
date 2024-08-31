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

namespace Turahe\Core\Macros\Arr;

class CastValuesAsString
{
    /**
     * Cast the provided array values as string
     *
     * @param  array  $array
     * @return array
     */
    public function __invoke($array)
    {
        return array_map(fn ($value) => (string) $value, $array);
    }
}
