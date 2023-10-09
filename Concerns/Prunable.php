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

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Prunable as LaravelPrunable;

/** @mixin \Modules\Core\Models\Model */
trait Prunable
{
    use LaravelPrunable;

    /**
     * Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return static::where('deleted_at', '<=', now()->subDays(
            config('core.soft_deletes.prune_after')
        ));
    }
}
