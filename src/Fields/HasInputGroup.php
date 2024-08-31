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

trait HasInputGroup
{
    /**
     * A custom icon to be incorporated in input group
     */
    public ?string $icon = null;

    /**
     * Input group append - right
     */
    public function inputGroupAppend(string $value): static
    {
        if ($this->supportsInputGroup()) {
            $this->withMeta(['inputGroupAppend' => $value]);
        }

        return $this;
    }

    /**
     * Input group prepend - left
     */
    public function inputGroupPrepend(string $value): static
    {
        if ($this->supportsInputGroup()) {
            $this->withMeta(['inputGroupPrepend' => $value]);
        }

        return $this;
    }

    /**
     * Checks whether the field support input group
     */
    public function supportsInputGroup(): bool
    {
        return property_exists($this, 'supportsInputGroup') && (bool) $this->supportsInputGroup;
    }

    /**
     * Append icon to the field
     */
    public function appendIcon(string $icon): static
    {
        return $this->icon($icon, true);
    }

    /**
     * Prepend icon to the field
     */
    public function prependIcon(string $icon): static
    {
        return $this->icon($icon, false);
    }

    /**
     * Custom input group icon
     */
    public function icon(string $icon, bool $append = true): static
    {
        if ($this->supportsInputGroup()) {
            $this->icon = $icon;
            $method = $append ? 'inputGroupAppend' : 'inputGroupPrepend';

            $this->{$method}(true);
        }

        return $this;
    }
}
