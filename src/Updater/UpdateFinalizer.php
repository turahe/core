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

namespace Turahe\Core\Updater;

use Turahe\Core\Application;
use Illuminate\Support\Facades\Artisan;

class UpdateFinalizer
{
    /**
     * Check whether finalization of the update is needed.
     */
    public function needed(): bool
    {
        return version_compare(
            $this->getCachedCurrentVersion(),
            Application::VERSION,
            '<'
        );
    }

    /**
     * Run the update finalizer.
     */
    public function run(): bool
    {
        if (! $this->needed()) {
            return false;
        }

        $this->runFinalizeCommands();
        $this->runPatchers();

        settings([
            '_version'           => Application::VERSION,
            '_last_updated_date' => date('Y-m-d H:i:s'),
            '_updated_from'      => $this->getCachedCurrentVersion(),
        ]);

        $this->runOptimizer();
        $this->restartQueue();

        return true;
    }

    /**
     * Get the cached current version.
     */
    public function getCachedCurrentVersion(): string
    {
        return settings('_version') ?: ($_SERVER['_VERSION'] ?? '1.0.7');
    }

    /**
     * Optimize the application.
     */
    protected function runOptimizer(): void
    {
        $command = config('updater.optimize');

        if ($command && ! app()->runningUnitTests() && app()->isProduction()) {
            $this->executeCommands([$command]);
        }
    }

    /**
     * Restart the queue.
     */
    protected function restartQueue(): void
    {
        if (config('updater.restart_queue')) {
            // Restart the queue (if configured)
            try {
                Artisan::call('queue:restart');
            } catch (\Exception) {
            }
        }
    }

    /**
     * Run the finalizer commands.
     */
    protected function runFinalizeCommands(): void
    {
        $this->executeCommands(config('updater.commands.finalize'));
    }

    /**
     * Execute the updates patchers.
     */
    protected function runPatchers(): void
    {
        app(Migration::class)->patchers()
            // Get all the versions starting from current cached (excluding current cached as this one is already executed)
            // between the latest available update for the current version (including current)
            ->filter(
                fn ($patch) => ! (version_compare($patch->version(), $this->getCachedCurrentVersion(), '<=') ||
                    version_compare($patch->version(), Application::VERSION, '>'))
            )
            ->filter->shouldRun()
            ->each->run();
    }

    /**
     * Run the given finalizer commands.
     */
    protected function executeCommands(array $commands): void
    {
        foreach ($commands as $command) {
            $name = is_array($command) ? $command['class'] : $command;
            $params = is_array($command) ? $command['params'] ?? [] : [];

            Artisan::call($name, $params);
        }
    }
}
