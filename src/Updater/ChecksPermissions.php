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

use Symfony\Component\Finder\Finder;

trait ChecksPermissions
{
    /**
     * @var callable|null
     */
    protected static $permissionsCheckerFinderUsing;

    /**
     * @var callable|null
     */
    protected static $checkPermissionsUsing;

    /**
     * Add custom permissions checker.
     */
    public static function checkPermissionsUsing(?callable $callable): void
    {
        static::$checkPermissionsUsing = $callable;
    }

    /**
     * Check a given directory recursively if all files are writeable.
     */
    protected function checkPermissions(string $path, array $excludedFolders): bool
    {
        $finder = $this->getPermissionsCheckerFinder($path, $excludedFolders);

        if (static::$checkPermissionsUsing) {
            return call_user_func_array(static::$checkPermissionsUsing, [$finder, $path, $excludedFolders]);
        }

        $passes = true;

        foreach ($finder as $file) {
            if ($file->isWritable() === false) {
                $passes = false;

                break;
            }
        }

        return $passes;
    }

    /**
     * Get the finder instance for the permissions checker
     */
    protected function getPermissionsCheckerFinder(string $path, array $excludedFolders): Finder
    {
        if (static::$permissionsCheckerFinderUsing) {
            return call_user_func_array(static::$permissionsCheckerFinderUsing, [$path]);
        }

        return (new Finder)->exclude($excludedFolders)->notName('worker.log')->in($path);
    }

    /**
     * Provide custom permissions checker Finder instance
     */
    public static function providePermissionsCheckerFinderUsing(?callable $callback): void
    {
        static::$permissionsCheckerFinderUsing = $callback;
    }
}
