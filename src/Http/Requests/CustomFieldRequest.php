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

namespace Turahe\Core\Http\Requests;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Turahe\Core\Facades\Fields;
use Turahe\Core\Rules\UniqueRule;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Models\CustomField;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Http\FormRequest;
use Turahe\Core\Contracts\Resources\AcceptsCustomFields;
use Turahe\Core\Contracts\Resources\AcceptsUniqueCustomFields;

class CustomFieldRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'resource_name' => [
                'required',
                'string',
                Rule::in(
                    Innoclapps::registeredResources()
                        ->whereInstanceOf(AcceptsCustomFields::class)
                        ->map(fn ($resource) => $resource->name())
                ),
            ],
            'label'      => 'required|string|max:191',
            'field_type' => [Rule::requiredIf($this->isCreation()), Rule::in(Fields::customFieldsTypes())],
            'is_unique'  => [
                'sometimes',  'nullable', 'boolean', Rule::prohibitedIf(
                    function () {
                        if (! $this->isCreation()) {
                            return false;
                        }

                        if (! Innoclapps::registeredResources()->first(
                            fn ($resource) => $resource->name() === $this->resource_name
                        ) instanceof AcceptsUniqueCustomFields) {
                            return true;
                        }

                        return ! in_array($this->field_type, Fields::getUniqueableCustomFieldsTypes());
                    }
                ),
            ],
            'field_id' => $this->getFieldIdRules(),
            'options'  => ['nullable', 'array', function (string $attribute, mixed $value, Closure $fail) {
                $customField = ! $this->isCreation() ?
                    CustomField::find($this->route('custom_field')) :
                    null;

                $fieldType = $this->isCreation() ? $this->field_type : $customField->field_type;

                $optionNames = array_filter(Arr::pluck($value, 'name'));

                if (! in_array($fieldType, Fields::getOptionableCustomFieldsTypes()) && count($optionNames) > 0) {
                    $fail('core::fields.validation.refuses_options')->translate();
                }

                if (in_array($fieldType, Fields::getOptionableCustomFieldsTypes()) && empty($optionNames)) {
                    $fail('core::fields.validation.requires_options')->translate();
                }

                if (count($optionNames) !== count(array_unique($optionNames))) {
                    $fail('All field options must be unique.');
                }
            }],
            'options.*.data' => 'nullable|array',
        ];
    }

    /**
     * Get the field_id attribute rules.
     */
    protected function getFieldIdRules(): array
    {
        // Not rules as the field_id can't be updated once the field is created
        if (! $this->isCreation()) {
            return [];
        }

        $prefix = config('fields.custom_fields.prefix');

        return [
            'bail',
            'required',
            'min:'.(3 + strlen($prefix)), // the user is required to enter at least 3 characters
            'max:64', // https://dev.mysql.com/doc/refman/5.7/en/identifier-length.html
            'regex:/^[a-z_]+$/',
            'starts_with:'.$prefix,
            UniqueRule::make(CustomField::class, 'custom_field')->where('resource_name', $this->resource_name),
            function (string $attribute, mixed $value, Closure $fail) {
                $resource = Innoclapps::resourceByName($this->resource_name);

                // First we will check if database column exists
                if (Schema::hasColumn(app($resource::$model)->getTable(), $value)) {
                    return $fail('core::fields.validation.exist')->translate();
                }

                // Finally, we will check if there is actually field with the same attribute/id
                // defined in the resource fields, includes default fields and custom fields
                if ($resource->getFields($this)->find($value)) {
                    return $fail('core::fields.validation.exist')->translate();
                }
            },
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'field_type.in'   => __('core::fields.validation.field_type_invalid'),
            'field_id.regex'  => __('core::fields.validation.field_id_invalid'),
            'field_id.unique' => __('core::fields.validation.exist'),
        ];
    }

    /**
     * Check whether is custom field creation request
     */
    protected function isCreation(): bool
    {
        return $this->isMethod('POST');
    }
}
