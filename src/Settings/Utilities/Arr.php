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

namespace Turahe\Core\Settings\Utilities;

use UnexpectedValueException;
use Illuminate\Support\Arr as BaseArr;

class Arr extends BaseArr
{
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array  &$array
     * @param  string  $key
     * @param  mixed  $value
     */
    public static function set(&$array, $key, $value): array
    {
        $segments = explode('.', $key);
        $key = array_pop($segments);

        // iterate through all of $segments except the last one
        foreach ($segments as $segment) {
            if (! array_key_exists($segment, $array)) {
                $array[$segment] = [];
            } elseif (! is_array($array[$segment])) {
                throw new UnexpectedValueException('Non-array segment encountered');
            }

            $array = &$array[$segment];
        }

        $array[$key] = $value;

        return $array;
    }
}
