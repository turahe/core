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

namespace Modules\Core\Resource;

use Closure;
use Modules\Core\Facades\Zapier;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Models\CustomField;
use Modules\Core\Models\CustomFieldOption;
use Modules\Core\Fields\CustomFieldService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Fields\CustomFieldResourceCollection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Core\Contracts\Resources\AcceptsCustomFields;

/** @mixin \Modules\Core\Models\Model */
trait Resourceable
{
    public static array $beforeSyncCustomFieldOptions = [];

    public static array $afterSyncCustomFieldOptions = [];

    /**
     * Boot the resource model
     */
    protected static function bootResourceable(): void
    {
        if (! Innoclapps::isAppInstalled() || ! $resource = static::resource()) {
            return;
        }

        static::bootFieldsEvents();

        if ($resource instanceof AcceptsCustomFields) {
            static::bootCustomFields();
        }

        if ($resource::$hasZapierHooks === true) {
            static::bootZapierHooks();
        }
    }

    /**
     * Boot the resource Zapier hooks
     */
    protected static function bootZapierHooks(): void
    {
        foreach (Zapier::supportedActions() as $event) {
            static::{$event}(function ($model) use ($event) {
                Zapier::queue($event, $model->getKey(), static::resource());
            });
        }
    }

    /**
     * Boot the fields model events
     */
    protected static function bootFieldsEvents(): void
    {
        // Available events from the Field trait
        $events = ['creating', 'created', 'updating', 'updated', 'deleting', 'deleted'];

        foreach ($events as $event) {
            static::{$event}(function ($model) use ($event) {
                $fields = static::resource()->resolveFields();

                $fields->each(function ($field) use ($model, $event) {
                    $method = 'record'.ucfirst($event);

                    $field->{$method}($model);
                });
            });
        }
    }

    /**
     * Register event for when before sync custom field options
     */
    public static function beforeSyncCustomFieldOptions(Closure $callback)
    {
        static::$beforeSyncCustomFieldOptions[static::class][spl_object_hash($callback)] = $callback;
    }

    /**
     * Register event for when after sync custom field options
     */
    public static function afterSyncCustomFieldOptions(Closure $callback)
    {
        static::$afterSyncCustomFieldOptions[static::class][spl_object_hash($callback)] = $callback;
    }

    /**
     * A model can have many associated resources
     */
    public function associatedResources(): array
    {
        $associations = [];

        foreach (static::resource()->availableAssociations() as $resource) {
            $associations[$resource->name()] = $this->{$resource->associateableName()};
        }

        return $associations;
    }

    /**
     * Check whether all the model associations are loaded
     */
    public function associationsLoaded(): bool
    {
        $associateables = static::resource()->availableAssociations();
        $totalAssociateables = count($associateables);

        $totalLoaded = $associateables->reduce(function ($carry, $resource) {
            return $this->relationLoaded($resource->associateableName()) ? ($carry + 1) : $carry;
        }, 0);

        return $totalAssociateables > 0 && $totalAssociateables === $totalLoaded;
    }

    /**
     * Get the model related resource instance
     *
     * @return \Modules\Core\Resource\Resource
     */
    public static function resource()
    {
        return Innoclapps::resourceByModel(static::class);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @return static
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        // Because model may be initialized without attributes in this case
        // first, we will check if there are attributes, then will merge the non-relation
        // custom field field_id's as fillable attributes
        if (static::resource() instanceof AcceptsCustomFields &&
                count($attributes) > 0 &&
                ! static::isUnguarded() &&
                count($this->getFillable()) > 0) {
            $this->fillable(array_unique(
                array_merge(
                    $this->getFillable(),
                    static::getCustomFields()->fillable()
                )
            ));
        }

        return parent::fill($attributes);
    }

    /**
     * Get the casts array.
     */
    public function getCasts(): array
    {
        return array_merge(parent::getCasts(), static::getCustomFields()->modelCasts());
    }

    /**
     * Boot the trait and register the necessary events
     */
    protected static function bootCustomFields(): void
    {
        static::bootCustomFieldsWithOptions();
    }

    /**
     * Get the model searchable columns.
     */
    public static function getSearchableColumns(): array
    {
        // Because of performance reasons, allow only unique custom fields to be searchable,
        // the user can search via the top search bar or via select async fields
        $columns = static::getCustomFields()->filter->isUnique()
            ->mapWithKeys(
                fn (CustomField $field) => [$field->field_id => 'like']
            );

        return array_merge(static::$searchableColumns, $columns->all());
    }

    /**
     * Boot the custom fields with options
     */
    protected static function bootCustomFieldsWithOptions(): void
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                foreach (static::getCustomFields()->optionable()->filter->isMultiOptionable() as $field) {
                    $model->{$field->relationName}()->detach();
                }
            }
        });
    }

    /**
     * Get the custom fields service instance.
     */
    protected static function getCustomFieldService(): CustomFieldService
    {
        return once(function () {
            return new CustomFieldService();
        });
    }

    /**
     * Get the model custom fields.
     */
    public static function getCustomFields(): CustomFieldResourceCollection
    {
        if (! static::resource()) {
            return new CustomFieldResourceCollection([]);
        }

        return static::getCustomFieldService()->forResource(
            static::resource()->name()
        );
    }

    /**
     * Determine if a relation exists in dynamic relations list
     */
    public static function getCustomFieldByRelationship(string $name): ?CustomField
    {
        return static::getCustomFields()
            ->optionable()
            ->firstWhere('relationName', $name);
    }

    /**
     * Create new custom field multi value options relation.
     */
    protected function newMultiValueOptionCustomFieldRelation(CustomField $field): MorphToMany
    {
        $instance = $this->newRelatedInstance(CustomFieldOption::class);

        return $this->newMorphToMany(
            $instance->newQuery(),
            $this,
            'model',
            'model_has_custom_field_options',
            'model_id',
            'option_id',
            $this->getKeyName(),
            $instance->getKeyName(),
            $field->relationName,
            false
        )->wherePivot('custom_field_id', $field->id);
    }

    /**
     * Create new custom field single value options relation.
     */
    protected function newSingleValueOptionCustomFieldRelation(CustomField $field): BelongsTo
    {
        $instance = $this->newRelatedInstance(CustomFieldOption::class);

        return $this->newBelongsTo(
            $instance->newQuery(),
            $this,
            $field->field_id,
            $instance->getKeyName(),
            $field->relationName
        );
    }

    /**
     * If the key exists in relations then
     * return call to relation or else
     * return the call to the parent
     *
     * @todo  in future use https://laravel.com/docs/10.x/eloquent-relationships#dynamic-relationships
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (! is_null(static::getCustomFieldByRelationship($name))) {
            if ($this->relationLoaded($name)) {
                return $this->relations[$name];
            }

            return $this->getRelationshipFromMethod($name);
        }

        return parent::__get($name);
    }

    /**
     * If the method exists in relations then
     * return the relation or else
     * return the call to the parent
     *
     * @todo  in future use https://laravel.com/docs/10.x/eloquent-relationships#dynamic-relationships
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (static::resource() && $field = static::getCustomFieldByRelationship($name)) {
            if (! $field->isMultiOptionable()) {
                return $this->newSingleValueOptionCustomFieldRelation($field);
            }

            return $this->newMultiValueOptionCustomFieldRelation($field);
        }

        return parent::__call($name, $arguments);
    }
}
