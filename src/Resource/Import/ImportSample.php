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

namespace Turahe\Core\Resource\Import;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Turahe\Core\Contracts\Fields\Dateable;
use Turahe\Core\Fields\Field;
use Turahe\Core\Fields\FieldsCollection;
use Turahe\Core\Resource\Resource;

class ImportSample implements FromArray
{
    use ProvidesImportableFields;

    /**
     * Create new Import instance.
     */
    public function __construct(protected Resource $resource) {}

    /**
     * Resolve the fields for the sample data
     */
    public function resolveSampleFields(): FieldsCollection
    {
        return $this->resolveFields()->reject(
            fn ($field) => $field->excludeFromImportSample
        );
    }

    /**
     * Download sample
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download()
    {
        return Excel::download($this, 'sample.csv');
    }

    /**
     * Creates the sample data rows
     */
    public function array(): array
    {
        return [
            $this->getHeadings(),
            $this->getRow(),
        ];
    }

    /**
     * Get sample headings by fields
     */
    public function getHeadings(): array
    {
        return $this->resolveSampleFields()->map(function (Field $field) {
            if ($field instanceof Dateable) {
                return $field->label.' ('.config('app.timezone').')';
            }

            return $field->label;
        })->all();
    }

    /**
     * Prepares import sample row
     */
    public function getRow(): array
    {
        return $this->resolveSampleFields()->reduce(function ($carry, $field) {
            $carry[] = $field->sampleValueForImport();

            return $carry;
        }, []);
    }

    /**
     * Provide the resource fields
     */
    public function fields(): FieldsCollection
    {
        return $this->resource->resolveFields();
    }
}
