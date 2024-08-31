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

namespace Turahe\Core\Synchronization\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Turahe\Core\Models\Synchronization;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Turahe\Core\Synchronization\Exceptions\InvalidSyncNotificationURLException;

class RefreshWebhookSynchronizations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Synchronization::with('synchronizable')
            ->withoutOAuthAuthenticationRequired()
            ->where(function ($query) {
                return $query->enabled()
                    ->where(function ($query) {
                        return $query->whereNull('expires_at')->orWhere('expires_at', '<', now()->addDays(2));
                    })->whereNotNull('resource_id');
            })->get()->each(function ($synchronization) {
                try {
                    $synchronization->refreshWebhook();
                } catch (InvalidSyncNotificationURLException) {
                    $synchronization->stopSync(
                        'We were unable to verify the notification URL for changes, make sure that your installation is publicly accessible, your installation URL starts with "https" and using valid SSL certificate.'
                    );
                }
            });
    }
}
