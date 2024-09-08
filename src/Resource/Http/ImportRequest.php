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

namespace Turahe\Core\Resource\Http;

use Turahe\Core\Fields\FieldsCollection;

class ImportRequest extends ResourcefulRequest
{
    /**
     * The row number the request is intended for
     */
    public ?int $rowNumber = null;

    protected ?FieldsCollection $fields = null;

    /**
     * The original import data
     */
    protected array $originalImport = [];

    /**
     * Get fields for the import.
     */
    public function fields(): FieldsCollection
    {
        return $this->fields;
    }

    /**
     * Get the authorized fields for import.
     */
    public function authorizedFields(): FieldsCollection
    {
        return $this->fields();
    }

    /**
     * Set the fields for the import request.
     */
    public function setFields(FieldsCollection $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Get the original row data.
     */
    public function original(): array
    {
        return $this->originalImport;
    }

    /**
     * Set the original row data.
     */
    public function setOriginal(array $row): static
    {
        $this->originalImport = $row;

        return $this;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
        ];
    }

    /**
     * Get the error messages for the current resource request.
     */
    public function messages(): array
    {
        return [
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void {}

    /**
     * Validate the class instance.
     */
    public function validateResolved(): void {}
}
