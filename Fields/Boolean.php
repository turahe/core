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

use Modules\Core\Table\BooleanColumn;
use Modules\Core\Contracts\Fields\Customfieldable;

class Boolean extends Field implements Customfieldable
{
    /**
     * Field component
     */
    public ?string $component = 'boolean-field';

    /**
     * Checkbox checked value
     */
    public mixed $trueValue = true;

    /**
     * Checkbox unchecked value
     */
    public mixed $falseValue = false;

    /**
     * Custom boot function
     *
     * @return void
     */
    public function boot()
    {
        $this->provideSampleValueUsing(fn () => 1);
    }

    /**
     * Checkbox checked value
     */
    public function trueValue(mixed $val): static
    {
        $this->trueValue = $val;

        return $this;
    }

    /**
     * Checkbox unchecked value
     */
    public function falseValue(mixed $val): static
    {
        $this->falseValue = $val;

        return $this;
    }

    /**
     * Resolve the field value for export
     * The export value should be in the original database value
     * not e.q. Yes or No
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return string|null
     */
    public function resolveForExport($model)
    {
        return $this->resolve($model);
    }

    /**
     * Resolve the displayable field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        $value = parent::resolveForDisplay($model);

        return $value === $this->trueValue ? __('core::app.yes') : __('core::app.no');
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
        $table->boolean($fieldId)->nullable()->default(false);
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): BooleanColumn
    {
        return tap(new BooleanColumn($this->attribute, $this->label), function ($column) {
            $column->trueValue($this->trueValue);
            $column->falseValue($this->falseValue);
        });
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'trueValue'  => $this->trueValue,
            'falseValue' => $this->falseValue,
        ]);
    }
}
