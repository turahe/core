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

namespace Modules\Core\Fields;

use Illuminate\Support\Arr;
use Modules\Core\Facades\Timezone as Facade;
use Modules\Core\Rules\ValidTimezoneCheckRule;
use Modules\Core\Contracts\Fields\Customfieldable;

class Timezone extends Field implements Customfieldable
{
    /**
     * Field component
     */
    public ?string $component = 'timezone-field';

    /**
     * Initialize Timezone field
     *
     * @param  string  $attribute
     * @param  string|null  $label
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label ?? __('core::app.timezone'));

        $this->rules(new ValidTimezoneCheckRule)
            ->provideSampleValueUsing(fn () => Arr::random(tz()->all()));
    }

    /**
     * Create the custom field value column in database
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @param  string  $fieldId
     * @return void
     */
    public static function createValueColumn($table, $fieldId)
    {
        $table->string($fieldId)->nullable();
    }

    /**
     * Provide the options intended for Zapier
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'timezones' => Facade::toArray(),
        ]);
    }
}
