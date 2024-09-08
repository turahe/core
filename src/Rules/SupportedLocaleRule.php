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
use ResourceBundle;

class SupportedLocaleRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! extension_loaded('intl')) {
            $passes = (bool) preg_match('/^[A-Za-z_]+$/', $value);
        } else {
            $passes = in_array($value, ResourceBundle::getLocales(''));
        }

        if (! $passes) {
            $fail('Invalid locale, locale name should be in format: "de" or "de_DE" or "pt_BR"');
        }
    }
}
