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

namespace Turahe\Core\Workflow;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessWorkflowAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Action $action)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Workflows::workflowRunning();

        try {
            $this->action->trigger()->runExecutionCallbacks($this->action);
            $this->action->run();
            $this->action->workflow->increment('total_executions');
        } finally {
            Workflows::workflowRunning(false);
        }
    }
}
