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

use Turahe\Core\Contracts\Fields\Customfieldable;
use Turahe\Core\Contracts\Fields\Dateable;
use Turahe\Core\Facades\Format;
use Turahe\Core\Fields\Dateable as DateableTrait;
use Turahe\Core\Placeholders\DatePlaceholder;
use Turahe\Core\Table\DateColumn;

class Date extends Field implements Customfieldable, Dateable
{
    use DateableTrait;

    /**
     * Field component
     */
    public ?string $component = 'date-field';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->rules(['nullable', 'date'])
            ->provideSampleValueUsing(fn () => date('Y-m-d'));
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
        $table->date($fieldId)->nullable();
    }

    /**
     * Resolve the displayable field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        return Format::date($model->{$this->attribute});
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Turahe\Core\Models\Model|null  $model
     * @return \Turahe\Core\Placeholders\DatePlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        return DatePlaceholder::make($this->attribute)
            ->value(fn () => $this->resolve($model))
            ->forUser($model?->user)
            ->description($this->label);
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): DateColumn
    {
        return new DateColumn($this->attribute, $this->label);
    }
}
