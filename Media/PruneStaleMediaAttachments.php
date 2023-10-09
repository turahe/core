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

namespace Modules\Core\Media;

use Modules\Core\Models\PendingMedia;

class PruneStaleMediaAttachments
{
    /**
     * Prune the stale attached media from the system.
     */
    public function __invoke(): void
    {
        PendingMedia::with('attachment')
            ->orderByDesc('id')
            ->where('created_at', '<=', now()->subDays(1))
            ->get()->each->purge();
    }
}
