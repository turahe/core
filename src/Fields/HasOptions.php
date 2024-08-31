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

namespace Turahe\Core\Fields;

use Turahe\Core\Resource\Resource;

trait HasOptions
{
    /**
     * Provided options
     *
     * @var mixed
     */
    public $options = [];

    /**
     * Add field options
     *
     * @param  array|callable|Illuminate\Support\Collection|Turahe\Core\Resource\Resource  $options
     * @return static
     */
    public function options(mixed $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Resolve the fields options
     *
     * @return array
     */
    public function resolveOptions()
    {
        if ($this->shoulUseZapierOptions()) {
            $options = $this->zapierOptions();
        } else {
            $options = with($this->options, function ($options) {
                if (is_callable($options)) {
                    $options = $options();
                }

                if ($options instanceof Resource) {
                    $options = $options->order($options->newQuery())->get();
                }

                return $options;
            });
        }

        return collect($options)->map(function ($label, $value) {
            return isset($label[$this->valueKey]) ? $label : [$this->labelKey => $label, $this->valueKey => $value];
        })->values()->all();
    }

    /**
     * Check whether the Zapier options should be used
     *
     * @return bool
     */
    protected function shoulUseZapierOptions()
    {
        return request()->isZapier() && method_exists($this, 'zapierOptions');
    }

    /**
     * Field additional meta
     */
    public function meta(): array
    {
        return array_merge([
            'valueKey'           => $this->valueKey,
            'labelKey'           => $this->labelKey,
            'optionsViaResource' => $this->options instanceof Resource ? $this->options->name() : null,
            'options'            => $this->resolveOptions(),
        ], $this->meta);
    }
}
