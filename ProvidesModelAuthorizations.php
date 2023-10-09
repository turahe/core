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

namespace Modules\Core;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Facades\Gate;

trait ProvidesModelAuthorizations
{
    /**
     * Get all defined authorizations for the model
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $without Exclude abilities from authorization
     * @return array|null
     *
     * @throws \ReflectionException
     */
    public function getAuthorizations($model, $without = [])
    {
        if ($policy = policy($model)) {
            return collect((new ReflectionClass($policy))->getMethods(ReflectionMethod::IS_PUBLIC))
                ->reject(function ($method) use ($without) {
                    return in_array($method->name, array_merge($without, ['denyAsNotFound', 'denyWithStatus', 'before']));
                })
                ->mapWithKeys(fn ($method) => [$method->name => Gate::allows($method->name, $model)])->all();
        }

        return null;
    }
}
