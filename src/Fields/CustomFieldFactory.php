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

use Turahe\Core\Contracts\Fields\Dateable;
use Turahe\Core\Filters\Filter;
use Turahe\Core\Models\CustomField;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Table\BelongsToColumn;
use Turahe\Core\Table\Column;
use Turahe\Core\Table\HasManyColumn;
use Turahe\Core\Table\MorphToManyColumn;

class CustomFieldFactory
{
    /**
     * The optionable custom field option id.
     */
    protected static string $optionId = 'id';

    /**
     * Filters namespace.
     */
    protected static string $filterNamespace = 'Turahe\Core\Filters';

    /**
     * Fields namespace.
     */
    protected static string $fieldNamespace = __NAMESPACE__;

    /**
     * Create new CustomFieldFactory instance.
     */
    public function __construct(protected string $resourceName) {}

    /**
     * Create fields from custom fields intended for filters
     */
    public function createFieldsForFilters(): array
    {
        $fields = [];

        foreach ($this->fields() as $field) {
            if ($instance = $this->createFilterInstance($field->field_type, $field)) {
                $fields[] = $instance;
            }
        }

        return $fields;
    }

    /**
     * Create field class from the given custom field
     */
    public static function createInstance(CustomField $field): Field
    {
        $instance = static::createFieldInstance(static::$fieldNamespace, $field);

        // Default is hidden on index
        $instance->tapIndexColumn(fn (Column $column) => $column->hidden(true));

        $rules = [];

        if ($instance->isMultiOptionable()) {
            $rules[] = 'array';

            $instance->tapIndexColumn(fn (HasManyColumn $column) => $column->queryAs('name as label'))
                ->saveUsing(function ($request, $requestAttribute, $value, $field) {
                    if (! $request->missing($requestAttribute)) {
                        return function ($model) use ($value, $field) {
                            return (new CustomFieldService)->syncOptionsForModel(
                                $model,
                                $field,
                                $value,
                                $model->wasRecentlyCreated ? 'create' : 'update'
                            );
                        };
                    }
                })->resolveUsing(fn ($model, $attribute) => $field->prepareRelatedOptions($model));
        }

        if ($instance::class === Text::class) {
            $rules[] = 'max:191';
        } elseif ($instance instanceof Dateable) {
            $instance->withMeta(['attributes' => [
                'clearable' => ! $instance->isRequired(app(ResourceRequest::class)),
            ]]);
        } elseif ($instance instanceof Url) {
            $rules[] = 'url';
            $rules[] = 'nullable';
        }

        if ($field->is_unique) {
            $rules[] = 'nullable';

            $instance->unique($field->resource()->model());
        }

        if ($instance->isOptionable()) {
            $instance->importUsing(function ($value, $row, $original, $field) {
                // The labelAsValue was unable to find id for the provided label
                // In this case, we will try to create the actual option in database
                if (is_null($value) && is_string($original[$field->attribute])) {
                    return [$field->attribute => static::getOptionIdViaLabel($field, $original[$field->attribute])];
                }

                return [$field->attribute => $value];
            });

            $instance->displayUsing(function ($model) use ($field, $instance) {
                return $instance->isMultiOptionable() ?
                    $model->{$field->relationName}->pluck('name')->implode(', ') :
                    $field->options->find($model->{$field->field_id})->name ?? '';
            });

            $instance->tapIndexColumn(
                fn (Column $column) => $column->select('swatch_color')->useComponent('table-data-option-column')
            );

            $instance->acceptLabelAsValue()->swapIndexColumn(
                fn () => $instance->isMultiOptionable() ?
                static::createColumnWhenMultiOptionable($field) :
                static::createColumnWhenSingleOptionable($field)
            );
        }

        $instance->rules(array_unique($rules));

        return $instance->setCustomField($field);
    }

    /**
     * Create new field class instance
     *
     * @return \Turahe\Core\Fields\Field|\Turahe\Core\Fields\Optionable|\Turahe\Core\Filters\Filter
     */
    protected static function createFieldInstance(
        string $namespace,
        CustomField $field,
        ?string $type = null,
    ): Field|Filter {
        $class = '\\'.$namespace.'\\'.($type ?? $field->field_type);
        $instance = (new $class($field->field_id, $field->label));

        if ($instance->isOptionable()) {
            $instance->valueKey(static::$optionId)->options($field->prepareOptions());
        }

        return $instance;
    }

    /**
     * Create filter instance from the given custom field
     */
    protected function createFilterInstance(string $type, CustomField $field): Filter|bool
    {
        if ($type == 'Textarea') {
            return false;
        } elseif ($type === 'Email') {
            $type = 'Text';
        } elseif ($type === 'Boolean') {
            $type = 'Radio';
        }

        $instance = static::createFieldInstance(static::$filterNamespace, $field, $type);

        if ($field->isMultiOptionable()) {
            $instance->query($this->multiOptionFilterQuery($field));
        } elseif ($field->field_type === 'Boolean') {
            $instance->options([true => __('core::app.yes'), false => __('core::app.no')]);
        }

        return $instance;
    }

    /**
     * Create table column when fields is multi optionable field
     */
    protected static function createColumnWhenMultiOptionable(CustomField $field): MorphToManyColumn
    {
        return new MorphToManyColumn($field->relationName, 'name', $field->label);
        // $callback = function ($model) use ($field) {
        //     return $model->{$field->relationName}
        //         ->map(fn ($option) => $option->label)->implode(', ');
        // };

        // return (new MorphToManyColumn($field->relationName, 'name', $field->label))->displayAs($callback);
    }

    /**
     * Create table column when field is single optionable field
     */
    protected static function createColumnWhenSingleOptionable(CustomField $field): BelongsToColumn
    {
        return new BelongsToColumn($field->relationName, 'name', $field->label);
    }

    /**
     * Create multi option filter query
     */
    protected function multiOptionFilterQuery(CustomField $field): callable
    {
        return function ($builder, $value, $condition, $sqlOperator) use ($field) {
            $method = strtolower($sqlOperator['operator']) === 'in' ? 'whereHas' : 'whereDoesntHave';

            return $builder->{$method}($field->relationName, function ($query) use ($value) {
                return $query->whereIn('id', $value);
            });
        };
    }

    /**
     * Get the resource custom fields
     *
     * @return \Illuminate\Support\Collection
     */
    protected function fields()
    {
        return (new CustomFieldService)->forResource($this->resourceName);
    }

    /**
     * Handle the label option when custom field is optionable
     *
     * @param  string  $label  The original provided label
     * @return array|int
     */
    protected static function getOptionIdViaLabel(Optionable $field, $label)
    {
        if (! $field->isMultiOptionable()) {
            return static::resolveImportLabelOption($field, $label);
        }

        return with([], function ($value) use ($field, $label) {
            $labels = explode(',', $label);

            array_walk($labels, 'trim');

            foreach ($labels as $label) {
                $value[] = static::resolveImportLabelOption($field, $label);
            }

            return $value;
        });
    }

    /**
     * Get custom field option by given option label
     *
     * @param  string  $label
     * @return int
     */
    protected static function resolveImportLabelOption(Optionable $field, $label)
    {
        // First check if the option actually exists in the options collection
        // Perhaps was created in the below create code block
        if ($option = $field->optionByLabel($label)) {
            return $option[$field->valueKey];
        }

        // If option not found, we will create this option for the custom field
        $customField = app(CustomFieldService::class)->createOptions([
            'name' => $label,
        ], $field->customField);

        // Get fresh options and update the value
        $options = $customField->options()->get();
        $value = $options->firstWhere('name', $label)->getKey();

        // Update field options and clear the cached collection
        $field->options($customField->prepareOptions($options))->clearCachedOptionsCollection();

        return $value;
    }
}
