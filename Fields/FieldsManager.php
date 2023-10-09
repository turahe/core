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

namespace Modules\Core\Fields;

use ReflectionClass;
use Illuminate\Support\Arr;
use Modules\Core\Facades\Fields;
use Illuminate\Support\Collection;
use Modules\Core\SubClassDiscovery;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Contracts\Fields\Customfieldable;
use Modules\Core\Contracts\Fields\UniqueableCustomfield;

class FieldsManager
{
    /**
     * Hold all groups and fields
     */
    protected static array $fields = [];

    /**
     * Loaded fields cache
     */
    protected static array $loaded = [];

    /**
     * The files that are custom field able
     */
    protected ?array $customFieldable = null;

    /**
     * Parsed custom fields cache
     */
    protected ?Collection $customFields = null;

    /**
     * Register fields with group.
     */
    public function group(string $group, mixed $provider): static
    {
        static::flushLoadedCache();

        if (! isset(static::$fields[$group])) {
            static::$fields[$group] = [];
        }

        static::$fields[$group][] = $provider;

        return $this;
    }

    /**
     * Check whether the given group has fields registered.
     */
    public function has(string $group): bool
    {
        return $this->load($group)->isNotEmpty();
    }

    /**
     * Add fields to the given group.
     */
    public function add(string $group, mixed $provider): static
    {
        return $this->group($group, $provider);
    }

    /**
     * Replace the group fields with the given fields.
     */
    public function replace(string $group, mixed $provider): static
    {
        static::$fields[$group] = [];

        return $this->group($group, $provider);
    }

    /**
     * Resolves fields for the given group and view.
     */
    public function resolve(string $group, string $view): FieldsCollection
    {
        return $this->{'resolve'.ucfirst($view).'Fields'}($group);
    }

    /**
     * Resolves fields for the given group and view for display.
     */
    public function resolveForDisplay(string $group, string $view): FieldsCollection
    {
        return $this->{'resolve'.ucfirst($view).'FieldsForDisplay'}($group);
    }

    /**
     * Resolve the create fields for display.
     */
    public function resolveCreateFieldsForDisplay(string $group): FieldsCollection
    {
        return $this->resolveCreateFields($group)
            ->reject(fn ($field) => $field->showOnCreation === false)
            ->values();
    }

    /**
     * Resolve the update fields for display.
     */
    public function resolveUpdateFieldsForDisplay(string $group): FieldsCollection
    {
        return $this->resolveUpdateFields($group)
            ->reject(fn ($field) => $field->showOnUpdate === false)
            ->values();
    }

    /**
     * Resolve the detail fields for display.
     */
    public function resolveDetailFieldsForDisplay(string $group): FieldsCollection
    {
        return $this->resolveDetailFields($group)
            ->reject(fn ($field) => $field->showOnDetail === false)
            ->values();
    }

    /**
     * Resolve the create fields for the given group.
     */
    public function resolveCreateFields(string $group): FieldsCollection
    {
        return $this->resolveAndAuthorize($group, Fields::CREATE_VIEW)
            ->filter->isApplicableForCreation()->values();
    }

    /**
     * Resolve the update fields for the given group.
     */
    public function resolveUpdateFields(string $group): FieldsCollection
    {
        return $this->resolveAndAuthorize($group, Fields::UPDATE_VIEW)
            ->filter->isApplicableForUpdate()->values();
    }

    /**
     * Resolve the detail fields for the given group.
     */
    public function resolveDetailFields(string $group): FieldsCollection
    {
        return $this->resolveAndAuthorize($group, Fields::DETAIL_VIEW)
            ->filter->isApplicableForDetail()->values();
    }

    /**
     * Resolve and authorize the fields for the given group.
     */
    public function resolveAndAuthorize(string $group, ?string $view = null): FieldsCollection
    {
        return $this->inGroup($group, $view)->filter->authorizedToSee();
    }

    /**
     * Resolve the fields intended for settings.
     */
    public function resolveForSettings(string $group, string $view): FieldsCollection
    {
        return $this->resolveAndAuthorize($group, $view)->reject(function ($field) use ($view) {
            return is_bool($field->excludeFromSettings) ?
                $field->excludeFromSettings :
                $field->excludeFromSettings === $view;
        })->values();
    }

    /**
     * Get all fields in specific group.
     */
    public function inGroup(string $group, ?string $view = null): FieldsCollection
    {
        if (isset(static::$loaded[$cacheKey = (string) $group.$view])) {
            return static::$loaded[$cacheKey];
        }

        $callback = function ($field, $index) use ($group, $view) {
            /**
             * Apply any custom attributes added by the user via settings
             */
            $field = $this->applyCustomizedAttributes($field, $group, $view);

            /**
             * Add field order if there is no customized order
             * This helps to sort them properly by default the way they are defined
             */
            $field->order ??= $index + 1;

            return $field;
        };

        return static::$loaded[$cacheKey] = $this->load($group)->map($callback)
            ->sortBy('order')
            ->when(true, function ($fields) {
                if (! Auth::check()) {
                    return $fields->reject(function ($field) {
                        return $field->authRequired === true;
                    });
                }

                return $fields;
            })->values();
    }

    /**
     * Save the customized fields
     */
    public function customize(mixed $data, string $group, string $view): void
    {
        $this->syncSettingsToOppositeView($data, $group, $view);

        settings()->set(
            $this->storageKey($group, $view),
            json_encode($data)
        );

        settings()->save();

        static::flushLoadedCache();
    }

    /**
     * Get the customized fields
     */
    public function customized(string $group, string $view, ?string $attribute = null): array
    {
        $attributes = json_decode(settings()->get($this->storageKey($group, $view), '[]'), true);

        if ($attribute) {
            return $attributes[$attribute] ?? [];
        }

        return $attributes;
    }

    protected function syncSettingsToOppositeView(mixed $data, string $group, string $view): void
    {
        // Technically, the details and the update views are the same, the details view option
        // exists only for the front-end, in this case, we need to make sure that the isRequired
        // customizable attribute should be propagated to the opposite view, as the fields for validation
        // when performing update are taken from the "update" view, as it's an update, there is no separate endpoints
        if (! in_array($view, [Fields::UPDATE_VIEW, Fields::DETAIL_VIEW])) {
            return;
        }

        $oppositeView = $view === Fields::UPDATE_VIEW ? Fields::DETAIL_VIEW : Fields::UPDATE_VIEW;

        $oppositeViewSettings = $this->customized($group, $oppositeView);

        foreach ($data as $attribute => $field) {
            if (! isset($oppositeViewSettings[$attribute])) {
                $oppositeViewSettings[$attribute] = [];
            }

            if (array_key_exists('isRequired', $field)) {
                $oppositeViewSettings[$attribute]['isRequired'] = $field['isRequired'];
            }

            if (array_key_exists('uniqueUnmarked', $field)) {
                $oppositeViewSettings[$attribute]['uniqueUnmarked'] = $field['uniqueUnmarked'];
            }

            if (count($oppositeViewSettings[$attribute]) === 0) {
                unset($oppositeViewSettings[$attribute]);
            }
        }

        if (count($oppositeViewSettings) > 0) {
            settings()->set(
                $this->storageKey($group, $oppositeView),
                json_encode($oppositeViewSettings)
            );
        }
    }

    /**
     * Purge the customized fields cache
     */
    public static function flushLoadedCache(): void
    {
        static::$loaded = [];
    }

    /**
     * Purge the registered fields cache
     */
    public static function flushRegisteredCache(): void
    {
        static::$fields = [];
    }

    /**
     * Get the available fields that can be used as custom fields
     */
    public function customFieldable(): Collection
    {
        return $this->customFields ??= collect($this->scanCustomFieldables())->mapWithKeys(function (string $className) {
            /** @var \Modules\Core\Fields\Field */
            $field = (new ReflectionClass($className))->newInstanceWithoutConstructor();

            return [$type = class_basename($className) => [
                'type'            => $type,
                'className'       => $className,
                'uniqueable'      => $field instanceof UniqueableCustomfield,
                'optionable'      => $field->isOptionable(),
                'multioptionable' => $field->isMultiOptionable(),
            ]];
        });
    }

    /**
     * Custom fields that are custom field ables.
     */
    protected function scanCustomFieldables(): array
    {
        return $this->customFieldable ??= SubClassDiscovery::make(Customfieldable::class)
            ->in(__DIR__)
            ->moduleable()
            ->find();
    }

    /**
     * Get the multi optionable custom fields types
     */
    public function getOptionableCustomFieldsTypes(): array
    {
        return $this->customFieldable()->where('optionable', true)->keys()->all();
    }

    /**
     * Get non optionable custom fields types
     */
    public function getNonOptionableCustomFieldsTypes(): array
    {
        return array_diff($this->customFieldsTypes(), $this->getOptionableCustomFieldsTypes());
    }

    /**
     * Get the available custom fields types
     */
    public function customFieldsTypes(): array
    {
        return $this->customFieldable()->keys()->all();
    }

    /**
     * Get the custom fields that can be marked as unique
     */
    public function getUniqueableCustomFieldsTypes(): array
    {
        return $this->customFieldable()->where('uniqueable', true)->keys()->all();
    }

    /**
     * Get the defined custom fields for the given resource.
     */
    public function getCustomFieldsForResource(string $resourceName): Collection
    {
        return (new CustomFieldService())->forResource($resourceName)->map(
            fn ($field) => CustomFieldFactory::createInstance($field)
        );
    }

    /**
     * Loaded the provided group fields.
     */
    protected function load(string $group): FieldsCollection
    {
        $fields = new FieldsCollection();

        foreach (static::$fields[$group] ?? [] as $provider) {
            if ($provider instanceof Field) {
                $provider = [$provider];
            }

            if (is_array($provider)) {
                $fields = $fields->merge($provider);
            } elseif (is_callable($provider)) { // callable, closure, __invoke
                $fields = $fields->merge(call_user_func($provider));
            }
        }

        return $fields->merge($this->getCustomFieldsForResource($group));
    }

    /**
     * Create customized storage key for settings
     */
    protected function storageKey(string $group, string $view): string
    {
        return "fields-{$group}-{$view}";
    }

    /**
     * Get the allowed customize able attributes
     */
    public function allowedCustomizableAttributes(): array
    {
        return [
            'order', 'showOnCreation', 'showOnUpdate',
            'showOnDetail', 'collapsed', 'isRequired', 'uniqueUnmarked',
        ];
    }

    /**
     * Get the allowed customize able attributes
     */
    public function allowedCustomizableAttributesForPrimary(): array
    {
        return ['order'];
    }

    /**
     * Get the allowed customizable attributes for the given field
     */
    protected function allowedCustomizeableAttributes(Field $field): array
    {
        // Protected the primary fields visibility and collapse options when direct API request
        // e.q. the field visibility is set to false when it must be visible because the field is marked as primary field

        return $field->isPrimary() ?
            $this->allowedCustomizableAttributesForPrimary() :
            $this->allowedCustomizableAttributes();
    }

    /**
     * Apply any customized options by user
     */
    protected function applyCustomizedAttributes(Field $field, string $group, ?string $view): Field
    {
        if (! $view) {
            return $field;
        }

        $attributes = Arr::only(
            $this->customized($group, $view, $field->attribute),
            $this->allowedCustomizeableAttributes($field)
        );

        foreach (['order', 'showOnCreation', 'showOnUpdate', 'showOnDetail', 'collapsed'] as $key) {
            if (array_key_exists($key, $attributes)) {
                $field->{$key} = $attributes[$key];
            }
        }

        if (array_key_exists('uniqueUnmarked', $attributes) &&
            $field->isUnique() &&
            $field->canUnmarkUnique === true &&
            $attributes['uniqueUnmarked'] ?? null == true) {
            $field->notUnique();
        }

        if (array_key_exists('isRequired', $attributes) && $attributes['isRequired'] == true) {
            $field->rules(['sometimes', 'required']);
        }

        return $field;
    }
}
