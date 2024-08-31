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

namespace Turahe\Core\Zapier;

use Illuminate\Support\Arr;
use Illuminate\Bus\Queueable;
use Turahe\Core\Models\ZapierHook;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessZapHookAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The code that Zapier throws when we need to unsubscribe from the hook.
     */
    const STATUS_CODE_UNSUBSCRIBE = 410;

    /**
     * Chunk size for the payload
     *
     * @var int
     */
    const CHUNK_SIZE = 50;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create new ProcessZapHooksAction instance.
     */
    public function __construct(protected string $hookUrl, protected mixed $payload)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        collect(Arr::wrap($this->payload))->chunk(static::CHUNK_SIZE)
            ->each(function ($data) {
                $response = Http::post($this->hookUrl, $data->all());

                if ($response->clientError() &&
                            $response->status() === static::STATUS_CODE_UNSUBSCRIBE) {
                    // Remove failed hook
                    $hook = ZapierHook::where('hook', $this->hookUrl)->first();

                    if ($hook) {
                        $hook->delete();
                    }
                } else {
                    $response->throw();
                }
            });
    }
}
