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

use Modules\Core\Facades\Format;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Table\DateTimeColumn;
use Modules\Core\Contracts\Fields\Dateable;
use Modules\Core\Resource\Http\ResourceRequest;
use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Fields\Dateable as DateableTrait;
use Modules\Core\Placeholders\DateTimePlaceholder;

class DateTime extends Field implements Customfieldable, Dateable
{
    use DateableTrait;

    /**
     * Field component
     */
    public ?string $component = 'date-time-field';

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->rules(['nullable', 'date'])
            ->provideSampleValueUsing(fn () => date('Y-m-d H:i:s'));
    }

    /**
     * Handle the resource record "creating" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordCreating($model)
    {
        if (! Innoclapps::isImportInProgress() || ! $model->usesTimestamps()) {
            return;
        }

        $timestampAttrs = [$model->getCreatedAtColumn(), $model->getUpdatedAtColumn()];
        $request = app(ResourceRequest::class);

        if ($request->has($this->requestAttribute()) &&
            in_array($this->attribute, $timestampAttrs) &&
            $model->isGuarded($this->attribute)) {
            $model->{$this->attribute} = $request->input($this->attribute);
        }
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
        $table->dateTime($fieldId)->nullable();
    }

    /**
     * Resolve the displayable field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string|null
     */
    public function resolveForDisplay($model)
    {
        return Format::dateTime($model->{$this->attribute});
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Modules\Core\Models\Model|null  $model
     * @return \Modules\Core\Placeholders\DateTimePlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        return DateTimePlaceholder::make($this->attribute)
            ->value(fn () => $this->resolve($model))
            ->forUser($model?->user)
            ->description($this->label);
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): DateTimeColumn
    {
        return new DateTimeColumn($this->attribute, $this->label);
    }
}
