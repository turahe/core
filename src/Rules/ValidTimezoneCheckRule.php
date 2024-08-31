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

namespace Turahe\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTimezoneCheckRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! empty($value) && ! in_array($value, tz()->all())) {
            $fail('validation.timezone')->translate();
        }
    }
}
