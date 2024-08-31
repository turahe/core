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

use Turahe\Core\Settings\DefaultSettings;

class EnsureDefaultSettingsArePresent
{
    public function __invoke(): void
    {
        if ($this->present()) {
            return;
        }

        settings()->flush();

        $settings = array_merge(DefaultSettings::get(), ['_seeded' => true]);

        foreach ($settings as $setting => $value) {
            settings()->set([$setting => $value]);
        }

        settings()->save();
    }

    private function present(): bool
    {
        return settings('_seeded') === true;
    }
}
