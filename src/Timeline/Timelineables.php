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

namespace Turahe\Core\Timeline;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Nwidart\Turahe\Facades\Module;
use Symfony\Component\Finder\Finder;

class Timelineables
{
    /**
     * @var \Illuminate\Support\Collection|null
     */
    protected static $models;

    /**
     * Discover and register the timelineables
     *
     * @return void
     */
    public static function discover()
    {
        $instance = new static;

        $timelineables = $instance->getTimelineables()->all();

        foreach ($instance->getSubjects() as $subject) {
            static::register($timelineables, $subject);
        }
    }

    /**
     * Register the given timelineables
     *
     * @param  string|array  $timelineables
     * @param  string  $subject
     * @return void
     */
    public static function register($timelineables, $subject)
    {
        Timeline::acceptsPinsFrom([
            'subject' => $subject,
            'as' => $subject::getTimelineSubjectKey(),
            'accepts' => array_map(function ($class) {
                return ['as' => $class::timelineKey(), 'timelineable_type' => $class];
            }, Arr::wrap($timelineables)),
        ]);
    }

    /**
     * Get the timelineables
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTimelineables()
    {
        return (new static)->getModels()
            ->filter(fn ($model) => static::isTimelineable($model))
            ->values();
    }

    /**
     * Check whether the given model is timelineable
     *
     * @param  \Turahe\Core\Models\Model  $model
     * @return bool
     */
    public static function isTimelineable($model)
    {
        return in_array(Timelineable::class, class_uses_recursive($model));
    }

    /**
     * Check whether the given model has timeline
     *
     * @param  \Turahe\Core\Models\Model  $model
     * @return bool
     */
    public static function hasTimeline($model)
    {
        return in_array(HasTimeline::class, class_uses_recursive($model));
    }

    /**
     * Get the subjects
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSubjects()
    {
        return (new static)->getModels()->filter(function ($model) {
            return in_array(HasTimeline::class, class_uses_recursive($model));
        })->values();
    }

    /**
     * Get the application models
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getModels()
    {
        if (static::$models) {
            return static::$models;
        }

        $modulesPaths = collect(Module::allEnabled())
            ->map(fn ($module) => module_path($module->getLowerName(), 'Models'))
            ->filter(fn ($path) => file_exists($path))
            ->values()
            ->all();

        $paths = array_merge([app_path('Models')], $modulesPaths);
        $finder = (new Finder)->in($paths)->files()->name('*.php');

        return static::$models = collect($finder)
            ->map(function ($model) {
                if (str_contains($model->getRealPath(), config('modules.paths.modules'))) {
                    return config('modules.namespace').'\\'.str_replace(
                        ['/', '.php'],
                        ['\\', ''],
                        Str::after($model->getRealPath(), realpath(config('modules.paths.modules')).DIRECTORY_SEPARATOR)
                    );
                }

                return app()->getNamespace().str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($model->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
                );
            });
    }
}
