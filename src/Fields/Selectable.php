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

namespace Turahe\Core\Fields;

trait Selectable
{
    /**
     * Set async URL for searching
     */
    public function async(string $asyncUrl): static
    {
        return $this->withMeta([
            'asyncUrl' => $asyncUrl,
            // Automatically add placeholder "Type to search..." on async fields
            'attributes' => ['placeholder' => __('core::app.type_to_search')],
        ]);
    }

    /**
     * Set the URL to lazy load options when the field is first opened
     */
    public function lazyLoad(string $url, array $params = []): static
    {
        return $this->withMeta(['lazyLoad' => [
            'url' => $url,
            'params' => $params,
        ]]);
    }
}
