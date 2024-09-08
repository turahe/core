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

use Turahe\Core\Contracts\Resources\Resourceful;
use Turahe\Core\Fields\Field;
use Turahe\Core\Fields\FieldsCollection;
use Turahe\Core\Models\Model;
use Turahe\Core\Resource\Resource;

class ResourcefulRequest extends ResourceRequest
{
    use InteractsWithResourceFields;

    /**
     * Get the class of the resource being requested.
     */
    public function resource(): Resource
    {
        return tap(parent::resource(), function ($resource) {
            abort_if(! $resource instanceof Resourceful, 404);
        });
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return $this->authorizedFields()->reject(fn ($field) => empty($field->label))
            ->mapWithKeys(function (Field $field) {
                return [$field->requestAttribute() => html_entity_decode(strip_tags(trim($field->label)))];
            })->all();
    }

    /**
     * Get the error messages for the current resource request.
     */
    public function messages(): array
    {
        return array_merge($this->authorizedFields()->map(function (Field $field) {
            return $field->prepareValidationMessages();
        })->filter()->collapse()->all(), $this->messagesFromResource());
    }

    /**
     * Get the error messages that are defined from the resource class
     */
    public function messagesFromResource(): array
    {
        return $this->resource()->validationMessages();
    }

    /**
     * Get the resource authorized fields for the request.
     */
    public function authorizedFields(): FieldsCollection
    {
        if (! $this->isSaving()) {
            return new FieldsCollection;
        }

        return $this->fields()->filter(function (Field $field) {
            return ! $field->isReadOnly();
        });
    }

    /**
     * Get all the available fields for the request.
     */
    public function allFields(): FieldsCollection
    {
        if (! $this->isSaving()) {
            return new FieldsCollection;
        }

        return $this->resource()->setModel(
            $this->resourceId() ? $this->record() : null
        )->getFields();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        if (! $this->isSaving()) {
            return [];
        }

        return array_merge_recursive(
            $this->resource()->rules($this),
            $this->isCreateRequest() ?
                    $this->resource()->createRules($this) :
                    $this->resource()->updateRules($this),
            $this->authorizedFields()->mapWithKeys(function (Field $field) {
                return $this->isCreateRequest() ? $field->getCreationRules() : $field->getUpdateRules();
            })->all()
        );
    }

    /**
     * Check whether the current request is intended to persist data in storage.
     */
    public function isSaving(): bool
    {
        return ($this->isMethod('POST') && $this->route()->getActionMethod() === 'store') ||
                ($this->isMethod('PUT') && $this->route()->getActionMethod() === 'update');
    }

    /**
     * Find record for the currently set resource from unique custom fields.
     */
    public function findRecordFromUniqueCustomFields(): ?Model
    {
        $fields = $this->fields()->filter->isCustomField()->filter(
            fn ($field) => $field->customField->is_unique
        );

        foreach ($fields as $field) {
            if ($record = $this->resource()->finder()->match([
                $field->attribute => $this->input($field->requestAttribute()),
            ])) {
                return $record;
            }
        }

        return null;
    }
}
