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

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Turahe\Facades\Module;
use SplFileInfo;

class Migration
{
    /**
     * The directory name the patchers are stored.
     */
    protected string $patchersDir = 'patchers';

    /**
     * Initialize new Migration instance
     */
    public function __construct(protected Migrator $migrator) {}

    /**
     * Get all of the updates patchers
     *
     * @return \Illuminate\Support\Collection
     */
    public function patchers()
    {
        $filesystem = new Filesystem;

        return collect($filesystem->files(base_path($this->patchersDir)))
            ->when(
                Module::allEnabled(),
                fn ($collection) => $collection->push(
                    ...$this->retrieveModulesPatchers($filesystem)
                )
            )
            ->filter(
                fn (SplFileInfo $file) => str_ends_with($file->getRealPath(), '.php') &&
                     str_starts_with($file->getFilename(), 'Update')
            )
            ->values()
            ->map(fn (SplFileInfo $file) => $filesystem->getRequire($file->getRealPath()))
            ->sortBy(
                fn (UpdatePatcher $patch) => $patch->version()
            )
            ->values();
    }

    /**
     * Get patchers classes from modules.
     */
    protected function retrieveModulesPatchers(Filesystem $filesystem): array
    {
        $files = [];

        foreach (Module::allEnabled() as $module) {
            $patchersPath = module_path($module->getLowerName(), $this->patchersDir);

            if ($filesystem->isDirectory($patchersPath)) {
                $files = [...$files, ...$filesystem->files($patchersPath)];
            }
        }

        return $files;
    }

    /**
     * Check whether the application requires migrations to be run.
     */
    public function needed(): bool
    {
        $ran = $this->migrator->getRepository()->getRan();
        $all = $this->getAllMigrationFiles();

        if (count($all) > 0) {
            return count($all) > count($ran);
        }

        return false;
    }

    /**
     * Get an array of all of the migration files.
     */
    protected function getAllMigrationFiles(): array
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
    }

    /**
     * Get all of the migration paths.
     */
    protected function getMigrationPaths(): array
    {
        return array_merge(
            $this->migrator->paths(),
            [$this->getMigrationPath()]
        );
    }

    /**
     * Get the path to the migration directory.
     */
    protected function getMigrationPath(): string
    {
        return database_path('migrations');
    }
}
