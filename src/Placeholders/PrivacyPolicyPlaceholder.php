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

namespace Turahe\Core\Placeholders;

class PrivacyPolicyPlaceholder extends UrlPlaceholder
{
    /**
     * Initialize new PrivacyPolicyPlaceholder instance.
     */
    public function __construct(string $tag = 'privacy_policy')
    {
        parent::__construct(null, $tag);

        $this->description(__('core::app.privacy_policy'));
    }

    /**
     * Format the placeholder
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        return privacy_url();
    }
}
