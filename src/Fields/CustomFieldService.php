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

use Illuminate\Support\Arr;
use Turahe\Core\Models\Filter;
use Turahe\Core\Facades\Fields;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Models\CustomField;
use Turahe\Core\QueryBuilder\Parser;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Turahe\Core\Models\CustomFieldOption;
use Turahe\Core\Contracts\Fields\Customfieldable;

class CustomFieldService
{
    /**
     * Custom fields cache.
     */
    protected static ?CustomFieldResourcesCollection $cache = null;

    /**
     * Find custom field by given ID.
     */
    public function find(int $id): ?CustomField
    {
        return CustomField::find($id);
    }

    /**
     * Create new custom field in storage.
     */
    public function create(array $attributes): CustomField
    {
        $resource = Innoclapps::resourceByName($attributes['resource_name']);

        $this->createColumn(
            $resource->newModel(),
            $attributes['field_id'],
            $attributes['field_type'],
            $unique = array_key_exists('is_unique', $attributes) ? $attributes['is_unique'] : null,
        );

        // For seeders, do not pass the options array to the create method
        // As in Seeder, mass assignment protection is disabled
        $field = new CustomField([
            'resource_name' => $attributes['resource_name'],
            'label'         => $attributes['label'],
            'field_type'    => $attributes['field_type'],
            'field_id'      => $attributes['field_id'],
            'is_unique'     => $unique,
        ]);

        $field->save();

        if ($field->isOptionable()) {
            return $this->createOptions($attributes['options'], $field)->load('options');
        }

        static::flushCache();

        return $field;
    }

    /**
     * Create options for the given field.
     */
    public function createOptions(array $options, CustomField|int $field): CustomField
    {
        $field = is_int($field) ? $this->find($field) : $field;
        $options = isset($options[0]) ? $options : [$options];

        $this->prepareOptionsForInsert($options)
            ->each(function ($option, $index) use ($field) {
                $field->options()->create([
                    'name'          => $option['name'],
                    'display_order' => $option['display_order'] ?? $index + 1,
                    'swatch_color'  => $option['swatch_color'] ?? null,
                ]);
            });

        return $field;
    }

    /**
     * Update the field in storage.
     */
    public function update(array $attributes, int $id): CustomField
    {
        $field = $this->find($id);
        $field->fill($attributes);
        $field->save();

        if ($field->isOptionable()) {
            $this->handleFieldOptionsUpdate($field, $attributes['options']);
        }

        static::flushCache();

        return $field->load('options');
    }

    public function delete(int $id): bool
    {
        $field = $this->find($id);

        $this->deleteColumn($field);

        $retval = $field->delete();

        $this->handleDeletedFieldFiltersRules($field);
        static::flushCache();

        return $retval;
    }

    /**
     * Handle the field options update.
     */
    protected function handleFieldOptionsUpdate(CustomField $field, array $options): void
    {
        $optionsBeforeUpdate = $field->options;

        $this->prepareOptionsForInsert($options)->each(function ($option, $index) use ($field, $optionsBeforeUpdate) {
            $attributes = [
                'name'          => $option['name'],
                'display_order' => $option['display_order'] ?? $index + 1,
                'swatch_color'  => $option['swatch_color'] ?? null,
            ];

            if (isset($option['id'])) {
                $optionsBeforeUpdate->find($option['id'])->fill($attributes)->save();
            } else {
                $field->options()->create($attributes);
            }
        });

        $optionsBeforeUpdate->filter(function (CustomFieldOption $option) use ($options) {
            return ! in_array($option->id, Arr::pluck($options, 'id'));
        })->each(function (CustomFieldOption $option) use ($field) {
            if ($field->isNotMultiOptionable()) {
                // Update constraint
                $field->resource()->newModel()->withTrashed()->update([$field->field_id => null]);
            }

            $option->delete();
        });
    }

    /**
     * Get the given resource custom fields.
     */
    public function forResource(string $resourceName): CustomFieldResourceCollection
    {
        if (! isset(static::$cache)) {
            static::$cache = new CustomFieldResourcesCollection(
                CustomField::with('options')->get()->all()
            );
        }

        return static::$cache->forResource($resourceName);
    }

    /**
     * Sync the custom field options for the given model
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Turahe\Core\Contracts\Fields\Customfieldable  $field
     * @param  string  $action
     * @return void
     */
    public function syncOptionsForModel($model, Customfieldable&Field $field, array $attributes, $action)
    {
        $callbackAttributes = [$model, $field, $attributes, $action];

        collect($model::$beforeSyncCustomFieldOptions[$model::class] ?? [])->each->__invoke(
            ...$callbackAttributes
        );

        $model->{$field->customField->relationName}()->sync(
            collect($attributes)->mapWithKeys(function ($id) use ($field) {
                return [$id => ['custom_field_id' => $field->customField->id]];
            })
        );

        collect($model::$afterSyncCustomFieldOptions[$model::class] ?? [])->each->__invoke(
            ...$callbackAttributes
        );
    }

    /**
     * Flush the fieds cache.
     */
    public static function flushCache(): void
    {
        static::$cache = null;
    }

    /**
     * Create the custom field in database
     *
     * @param  \Illuminate\Database\Eloquent\Model  $relatedModel
     * @param  string  $fieldId
     * @return void
     */
    protected function createColumn($relatedModel, $fieldId, $type, ?bool $isUnique)
    {
        $field = Fields::customFieldable()[$type];

        Schema::table($relatedModel->getTable(), function (Blueprint $table) use ($field, $fieldId, $isUnique) {
            $field['className']::createValueColumn($table, $fieldId);

            if ($isUnique === true && $field['uniqueable']) {
                $table->unique($fieldId);
            }
        });
    }

    /**
     * Delete the given field column.
     */
    protected function deleteColumn(CustomField $field): void
    {
        $relatedModel = $field->resource()->newModel();

        if (! Schema::hasColumn($relatedModel->getTable(), $field->field_id)) {
            return;
        }

        Schema::table($relatedModel->getTable(), function (Blueprint $table) use ($field, $relatedModel) {
            if ($field->isOptionable() && ! app()->runningUnitTests()) {
                $key = DB::select(
                    DB::raw(
                        'SHOW KEYS
                            FROM '.DB::getTablePrefix().$relatedModel->getTable().'
                            WHERE Column_name=\''.$field->field_id.'\''
                    )->getValue(DB::connection()->getQueryGrammar())
                );

                $table->dropForeign($key[0]->Key_name);
            }

            $table->dropColumn($field->field_id);
        });
    }

    /**
     * Prepare the options for insert.
     */
    protected function prepareOptionsForInsert(array $options): Collection
    {
        return collect($options)->reject(fn ($option) => empty($option['name']))->unique('name');
    }

    /**
     * Handle the deleted custom field filter rules.
     */
    protected function handleDeletedFieldFiltersRules(CustomField $field): void
    {
        // When model with custom fields is deleted, we will get the filters
        // which most likely are using custom field and remove them from the query object
        Filter::where(
            'rules',
            'like',
            '%'.$field->field_id.'%'
        )
            ->get()
            ->each(function (Filter $filter) use ($field) {
                $rules = Arr::toObject($filter->rules);

                if (Parser::validate($rules)) {
                    $rules->children = $this->handleDeletedFieldFilterRules($rules->children, $field);

                    $filter->fill(['rules' => (array) $rules ?? []])->save();
                }
            });
    }

    /**
     * Handle the deleted field rules.
     */
    protected function handleDeletedFieldFilterRules(array &$rules, CustomField $field): array
    {
        foreach ($rules as $key => $rule) {
            if (Parser::isNested($rule)) {
                $rule->query->children = $this->handleDeletedFieldFilterRules($rule->query->children, $field);
            } else {
                if ($rule->query->rule == $field->field_id ||
                    (isset($rule->query->operand) && $rule->query->operand == $field->field_id)) {
                    unset($rules[$key]);
                }
            }

            // If the current rule in the loop is group, we will check if the
            // group is empty and if yes, we will actually remove the group from
            // the rules object as it won't be needed.
            if ($rule->type === 'group' && empty($rule->query->children)) {
                unset($rules[$key]);
            }
        }

        return array_values($rules);
    }
}
