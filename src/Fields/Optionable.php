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

use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class Optionable extends Field
{
    use ChangesKeys,
        HasOptions;

    /**
     * Cached options
     */
    protected ?Collection $cachedOptions = null;

    /**
     * Indicates whether the field accept string as value
     */
    public bool $acceptLabelAsValue = false;

    /**
     * Accept string value
     *
     * @return static
     */
    public function acceptLabelAsValue()
    {
        $this->acceptLabelAsValue = true;

        $callback = function ($value, $request, $validator, $input) {
            if ($this->isMultiOptionable()) {
                $value = $this->parseValueAsLabelViaMultiOptionable($value);
            } elseif (! is_numeric($value) && ! is_null($value)) {
                $value = $this->parseValueAsLabelViaOptionable($value, $input);
            }

            return $value;
        };

        return $this->prepareForValidation($callback);
    }

    /**
     * Get the sample value for the field
     *
     * @return string
     */
    public function sampleValue()
    {
        if (is_callable($this->sampleValueCallback)) {
            return call_user_func_array($this->sampleValueCallback, [$this->attribute]);
        }

        return $this->resolveOptions()[0][
           $this->acceptLabelAsValue ? $this->labelKey : $this->valueKey
        ] ?? '';
    }

    /**
     * Get option by given label
     *
     * @param  string  $label
     * @return mixed
     */
    public function optionByLabel($label)
    {
        $label = is_string($label) ? Str::lower($label) : $label;

        $callback = function ($option) use ($label) {
            $optionLabel = is_array($option) ? $option[$this->labelKey] : $option->{$this->labelKey};

            return (is_string($optionLabel) ? Str::lower($optionLabel) : $optionLabel) == $label;
        };

        return $this->getCachedOptionsCollection()->firstWhere($callback);
    }

    /**
     * Get cached options collection
     *
     * When importing data, the label as value function will be called
     * multiple times, we don't want all the queries executed multiple times
     * from the fields which are providing options from the database.
     */
    public function getCachedOptionsCollection(): Collection
    {
        if (! $this->cachedOptions) {
            $this->cachedOptions = collect($this->resolveOptions());
        }

        return $this->cachedOptions;
    }

    /**
     * Clear the cached options collection
     */
    public function clearCachedOptionsCollection(): static
    {
        $this->cachedOptions = null;

        return $this;
    }

    /**
     * Get the field value when label is provided
     *
     * @param  string  $label
     * @param  array  $input
     * @return mixed
     */
    protected function parseValueAsLabelViaOptionable($label, $input)
    {
        return $this->optionByLabel($label)[$this->valueKey] ?? null;
    }

    /**
     * Get the field value when label is provided for multi optionable fields
     */
    protected function parseValueAsLabelViaMultiOptionable(array|string|null $value): array
    {
        if (is_null($value)) {
            return [];
        }

        if (is_string($value)) {
            $value = Str::of($value)->explode(',')->map(fn ($content) => trim($content))->all();
        } elseif (is_int($value)) {
            $value = [$value];
        }

        foreach ($value as $key => $label) {
            if (! is_numeric($label)) {
                if ($option = $this->optionByLabel($label)) {
                    $value[$key] = $option[$this->valueKey] ?? null;
                } else {
                    // Invalid key provided
                    unset($value[$key]);
                }
            }
        }

        return array_filter(array_values($value));
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'acceptLabelAsValue' => $this->acceptLabelAsValue,
        ]);
    }
}
