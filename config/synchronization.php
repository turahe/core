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

return [
    /*
    |--------------------------------------------------------------------------
    | Synchronization interval definition
    |--------------------------------------------------------------------------
    |
    | For periodic synchronization like Google, the events by default
    | are synchronized every 3 minutes, the interval can be defined below in cron style.
    */
    'interval' => env('SYNC_INTERVAL', '*/3 * * * *'),
];
