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

namespace Modules\Core\Updater;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

abstract class UpdatePatcher
{
    /**
     * Run the patcher
     */
    abstract public function run(): void;

    /**
     * Check whether the patcher should run
     */
    abstract public function shouldRun(): bool;

    /**
     * Get the version number this patcher is intended for
     */
    public function version(): string
    {
        $versionFromFilename = $this->versionFromFilename();

        // semver
        if (str_contains($versionFromFilename, '.')) {
            return $versionFromFilename;
        }

        // 110 => 1.1.0
        return wordwrap($this->versionFromFilename(), 1, '.', true);
    }

    /**
     * Get the version from the filename.
     */
    public function versionFromFilename(): string
    {
        return Str::after($this->filenameWithoutExtension(), 'Update');
    }

    /**
     * Get the class filename without extension
     */
    protected function filenameWithoutExtension(): string
    {
        return str_replace('.php', '', basename((new ReflectionClass($this))->getFileName()));
    }

    /**
     * Get column indexes
     */
    protected function getColumnIndexes(string $table, string $column): array
    {
        return DB::select(
            DB::raw(
                'SHOW KEYS
                FROM '.DB::getTablePrefix().$table.'
                WHERE Column_name="'.$column.'"'
            )->getValue(DB::connection()->getQueryGrammar())
        );
    }
}
