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

namespace Turahe\Core\Database\State;

use ReflectionMethod;
use Turahe\Core\Facades\MailableTemplates;

class EnsureMailableTemplatesArePresent
{
    public function __invoke()
    {
        if (! MailableTemplates::requiresSeeding()) {
            return;
        }

        $mailables = MailableTemplates::get();

        foreach ($mailables as $mailable) {
            $mailable = new ReflectionMethod($mailable, 'seed');

            $mailable->invoke(null);
        }
    }
}
