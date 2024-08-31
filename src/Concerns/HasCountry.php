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

namespace Turahe\Core\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @mixin \Turahe\Core\Models\Model */
trait HasCountry
{
    /**
     * A model belongs to country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(\Turahe\Core\Models\Country::class);
    }
}
