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

use Illuminate\Support\Collection;
use Turahe\Core\Casts\ISO8601Date;
use Turahe\Core\Models\CustomField;
use Turahe\Core\Casts\ISO8601DateTime;

class CustomFieldResourceCollection extends Collection
{
    /**
     * Fillable attributes cache cache
     */
    protected ?array $fillable = null;

    /**
     * Castable fields cache
     */
    protected ?self $castable = null;

    /**
     * Model casts cache
     */
    protected ?array $modelCasts = null;

    /**
     * Get the optionable fields
     *
     * @return static
     */
    public function optionable()
    {
        return $this->filter->isOptionable();
    }

    /**
     * Get the fillable attributes for the model
     *
     * @return array
     */
    public function fillable()
    {
        if (is_null($this->fillable)) {
            $this->fillable = $this->filter->isNotMultiOptionable()->pluck('field_id')->all();
        }

        return $this->fillable;
    }

    /**
     * Get the model casts
     *
     * @return array
     */
    public function modelCasts()
    {
        if (is_null($this->modelCasts)) {
            $data = $this->castableFieldsData();

            $this->modelCasts = $this->castable()->mapWithKeys(function (CustomField $field) use ($data) {
                return [$field->field_id => $data[$field->field_type]];
            })->all();
        }

        return $this->modelCasts;
    }

    /**
     * Get the castable fields
     *
     * @return static
     */
    public function castable()
    {
        if (is_null($this->castable)) {
            $this->castable = $this->whereIn('field_type', array_keys($this->castableFieldsData()));
        }

        return $this->castable;
    }

    /**
     * Get the castable fields data
     *
     * @return array
     */
    protected function castableFieldsData()
    {
        return [
            'Text'     => 'string',
            'Textarea' => 'string',
            'Email'    => 'string',
            'Timezone' => 'string',
            'Date'     => ISO8601Date::class,
            'DateTime' => ISO8601DateTime::class,
            'Boolean'  => 'boolean',
            'Numeric'  => 'decimal:3',
            'Number'   => 'int',
            'Radio'    => 'int',
            'Select'   => 'int',
        ];
    }
}
