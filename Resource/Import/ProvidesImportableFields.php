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

namespace Modules\Core\Resource\Import;

use Modules\Core\Fields\FieldsCollection;

trait ProvidesImportableFields
{
    protected ?FieldsCollection $fields = null;

    /**
     * Provide the resource fields
     */
    abstract public function fields(): FieldsCollection;

    /**
     * Resolve the importable fields
     */
    public function resolveFields(): FieldsCollection
    {
        if (! $this->fields) {
            $this->fields = $this->filterFieldsForImport($this->fields());
        }

        return $this->fields;
    }

    /**
     * Filter fields for import
     */
    protected function filterFieldsForImport(FieldsCollection $fields): FieldsCollection
    {
        return $fields->reject(function ($field) {
            return $field->excludeFromImport || $field->isReadOnly();
        })->values();
    }
}
