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
use Illuminate\Support\Facades\Http;
use Turahe\Core\Facades\ReCaptcha;

class ValidRecaptchaRule implements ValidationRule
{
    /**
     * The endpoint to verify recaptcha
     */
    protected string $verifyEndpoint = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Http::asForm()->post($this->verifyEndpoint, [
            'secret' => ReCaptcha::getSecretKey(),
            'response' => $value,
        ])['success']) {
            $fail('validation.recaptcha')->translate();
        }
    }
}
