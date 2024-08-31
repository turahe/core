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

use Exception;
use Turahe\Core\Contracts\Presentable;

class GenericPlaceholder extends Placeholder
{
    /**
     * Format the placeholder
     *
     * @return string
     */
    public function format(?string $contentType = null)
    {
        if ($this->value instanceof Presentable) {
            return $this->value->display_name;
        }

        return $this->value;
    }

    /**
     * Serialize the placeholder for the front end
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        if (! $data['tag']) {
            throw new Exception('"tag" not provided for generic placeholder.');
        }

        return $data;
    }
}
