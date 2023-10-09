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

namespace Modules\Core\Fields;

trait ChecksForDuplicates
{
    /**
     * Duplicate checker data
     */
    protected array $checkDuplicatesWith = [];

    /**
     * Add duplicates checker data
     */
    public function checkPossibleDuplicatesWith(string $url, array $params, string $langKey): static
    {
        return $this->withMeta([
            'checkDuplicatesWith' => [
                'url'          => $url,
                'params'       => $params,
                'lang_keypath' => $langKey,
            ],
        ]);
    }
}
