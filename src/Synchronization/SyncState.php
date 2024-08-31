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

namespace Turahe\Core\Synchronization;

enum SyncState: string
{
    case DISABLED = 'disabled';
    case STOPPED = 'stopped';
    case ENABLED = 'enabled';
}
