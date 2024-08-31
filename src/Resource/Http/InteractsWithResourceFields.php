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
use Illuminate\Contracts\Validation\Validator;

trait InteractsWithResourceFields
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->setAuthorizedAttributes();

        $validator = $this->getValidatorInstance();

        $this->runValidationCallbacks($validator);

        // Laravel sets the validator data once the validator instance is created
        // In this case, we need to update the validator data with the new (possibly) modified data
        $validator->setData($this->all());
    }

    /**
     * Run the fields validation callbacks.
     */
    public function runValidationCallbacks(Validator $validator): static
    {
        $data = [];
        $original = $this->all();

        foreach ($this->fieldsForValidationCallback() as $field) {
            $attribute = $field->requestAttribute();

            $data[$attribute] = call_user_func_array(
                $field->validationCallback,
                [$this->input($attribute), $this, $validator, $original]
            );
        }

        return $this->merge($data);
    }

    /**
     * Get the fields applicable for validation callback.
     */
    protected function fieldsForValidationCallback(): FieldsCollection
    {
        return $this->authorizedFields()->reject(function ($field) {
            return is_null($field->validationCallback) || $this->missing($field->requestAttribute());
        });
    }

    /**
     * Set the authorized attributes for the request.
     */
    protected function setAuthorizedAttributes(): void
    {
        // We will get all available fields for the current
        // request and based on the fields authorizations we will set
        // the authorized attributes, useful for example, field is not authorized to be seen
        // but it's removed from the fields method and in this case, if we don't check this here
        // this attribute will be automatically allowed as it does not exists in the authorized fields section
        // for this reason, we check this from all the available fields
        $fields = $this->allFields();

        $this->replace(collect($this->all())->filter(function ($value, $attribute) use ($fields) {
            return with($fields->findByRequestAttribute($attribute), function ($field) {
                return $field ? ($field->authorizedToSee() && ! $field->isReadOnly()) : true;
            });
        })->all());
    }

    /**
     * Get the associteables attributes but without any custom fields.
     */
    public function associateables(): array
    {
        $fields = $this->authorizedFields();
        $associations = $this->resource()->availableAssociations();

        return collect($this->all())->filter(function ($value, $attribute) use ($associations, $fields) {
            // First, we will check if the attribute name is the special attribute "associations"
            if ($attribute === 'associations') {
                return true;
            }

            // Next, we will check if the attribute exists as available associateable
            // resource for the current resource, if exists, we will check if the resource is associateable
            // This helps to provide the associations on resources without fields defined
            $resource = $associations->first(function ($resource) use ($attribute) {
                return $resource->associateableName() === $attribute;
            });

            // If resource is found from the attribute and this resource
            // is associateble, we will return true for the filter
            if ($resource && $resource->isAssociateable()) {
                return true;
            }

            // Next, we will check if the attribute exists as field in the
            // authorized fields collection for the request
            $field = $fields->findByRequestAttribute($attribute);

            // Finally, we will check if it's a field and is multioptionable field
            return $field && $field->isMultiOptionable() && ! $field->isCustomField();
        })->all();
    }
}
