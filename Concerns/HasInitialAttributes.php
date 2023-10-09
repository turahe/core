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

namespace Modules\Core\Concerns;

use Illuminate\Support\Arr;

/** @mixin \Modules\Core\Models\Model */
trait HasInitialAttributes
{
    protected static array $extraInitialAttributes = [];

    /**
     * Boot HasInitialAttributes trait.
     */
    protected static function bootHasInitialAttributes(): void
    {
        static::creating(function ($model) {
            $model->mergeInitialAttributes();
        });
    }

    /**
     * Get the model initial attributes with dot notation.
     */
    abstract public static function getInitialAttributes(): array;

    /**
     * Add extra initial attributes to the model.
     */
    public static function withInitialAttributes(array $attributes): void
    {
        static::$extraInitialAttributes = array_merge(static::$extraInitialAttributes, $attributes);
    }

    /**
     * Merge the model initial attributes.
     */
    public function mergeInitialAttributes(): static
    {
        $defaults = array_merge(static::$extraInitialAttributes, static::getInitialAttributes());

        // Map the attributes with their actual value (casted)
        $attributes = collect($this->getAttributes())->map(function ($value, $key) {
            return $this->getAttribute($key);
        })->all();

        foreach ($defaults as $path => $value) {
            if (! Arr::has($attributes, $path) || blank(Arr::get($attributes, $path))) {
                Arr::set($attributes, $path, $value);
            }
        }

        $this->forceFill($attributes);

        return $this;
    }
}
