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

use Turahe\Core\Resource\Http\ResourceRequest;

class Domain extends Field
{
    /**
     * Field component
     */
    public ?string $component = 'domain-field';

    /**
     * This field support input group
     */
    public bool $supportsInputGroup = true;

    /**
     * Boot field
     *
     * Sets icon
     *
     * @return null
     */
    public function boot()
    {
        $this->provideSampleValueUsing(fn () => 'example.com')->prependIcon('Globe');
    }

    /**
     * Get the field value for the given request
     *
     * @param  string  $requestAttribute
     */
    public function attributeFromRequest(ResourceRequest $request, $requestAttribute): mixed
    {
        $value = parent::attributeFromRequest($request, $requestAttribute);

        return \Turahe\Core\Domain::extractFromUrl($value ?? '');
    }
}
