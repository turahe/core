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

namespace Turahe\Core;

use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class SubClassDiscovery
{
    use Makeable;

    protected array $directories = [];

    protected bool $inModules = false;

    protected string $path;

    protected string $namespace;

    public function __construct(protected string $subclass, null|string|array $directories = null)
    {
        $this->namespace = app()->getNamespace();

        $this->path = app_path();

        if ($directories) {
            $this->in($directories);
        }
    }

    public function moduleable(): static
    {
        $this->namespace = config('modules.namespace').'\\';
        $this->path = config('modules.paths.modules');

        return $this;
    }

    public function in(string|array $directories): static
    {
        $this->directories = (array) $directories;

        return $this;
    }

    public function find(): array
    {
        $classes = [];

        foreach ((new Finder)->in($this->directories)->files() as $class) {
            $class = $this->namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($class->getPathname(), $this->path.DIRECTORY_SEPARATOR)
            );

            if (is_subclass_of($class, $this->subclass) && ! (new ReflectionClass($class))->isAbstract()) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
