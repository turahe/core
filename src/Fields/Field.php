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

use Closure;
use Illuminate\Support\Arr;
use JsonSerializable;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\HasHelpText;
use Turahe\Core\Http\Resources\CustomFieldResource;
use Turahe\Core\Makeable;
use Turahe\Core\Models\CustomField;
use Turahe\Core\Placeholders\GenericPlaceholder;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Resource\Import\Import;
use Turahe\Core\Rules\UniqueResourceRule;

class Field extends FieldElement implements JsonSerializable
{
    use DisplaysOnIndex, HasHelpText, HasInputGroup, HasModelEvents, Makeable, ResolvesValue;

    /**
     * Default value
     *
     * @var mixed
     */
    public $value;

    /**
     * Field attribute / column name
     *
     * @var string
     */
    public $attribute;

    /**
     * Custom field request attribute
     *
     * @var string|null
     */
    public $requestAttribute;

    /**
     * Field label
     *
     * @var string
     */
    public $label;

    /**
     * Indicates how the help text is displayed, as icon or text
     */
    public string $helpTextDisplay = 'icon';

    /**
     * Whether the field is collapsed. E.q. view all fields
     */
    public bool $collapsed = false;

    /**
     * Validation rules
     */
    public array $rules = [];

    /**
     * Validation creation rules
     */
    public array $creationRules = [];

    /**
     * Validation import rules
     */
    public array $importRules = [];

    /**
     * Validation update rules
     */
    public array $updateRules = [];

    /**
     * Custom validation error messages
     */
    public array $validationMessages = [];

    /**
     * Whether the field is primary
     */
    public bool $primary = false;

    /**
     * Indicates whether the field is custom field
     */
    public ?CustomField $customField = null;

    /**
     * Emit change event when field value changed
     */
    public ?string $emitChangeEvent = null;

    /**
     * Is field read only
     *
     * @var bool|callable
     */
    public $readOnly = false;

    /**
     * Is the field hidden
     */
    public bool $displayNone = false;

    /**
     * Indicates whether the column value should be always included in the
     * JSON Resource for front-end
     */
    public bool $alwaysInJsonResource = false;

    /**
     * Prepare for validation callback
     *
     * @var callable|null
     */
    public $validationCallback;

    /**
     * Indicates whether the field is excluded from Zapier response
     */
    public bool $excludeFromZapierResponse = false;

    /**
     * Field order
     */
    public ?int $order;

    /**
     * Field column class
     */
    public string|Closure|null $colClass = null;

    /**
     * Field toggle indicator
     */
    public bool $toggleable = false;

    /**
     * Custom attributes provider for create/update
     *
     * @var callable|null
     */
    public $saveUsing;

    /**
     * Custom callback used to determine if the field is required.
     *
     * @var \Closure|bool
     */
    public $isRequiredCallback;

    /**
     * Field component
     */
    public ?string $component = null;

    /**
     * Indicates whether a unique field can be unmarked as unique
     */
    public bool $canUnmarkUnique = false;

    /**
     * Indicates that the field is available only for authRequired user.
     */
    public bool $authRequired = false;

    /**
     * Initialize new Field instance class
     *
     * @param  string  $attribute  field attribute
     * @param  string|null  $label  field label
     */
    public function __construct($attribute, $label = null)
    {
        $this->attribute = $attribute;

        $this->label = $label;

        $this->boot();
    }

    /**
     * Custom boot function
     *
     * @return void
     */
    public function boot() {}

    /**
     * Set field attribute
     *
     * @param  string  $attribute
     */
    public function attribute($attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set field label
     *
     * @param  string  $label
     */
    public function label($label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the field order
     */
    public function order(?int $order): static
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set the field column class
     */
    public function colClass(string|Closure|null $class): static
    {
        $this->colClass = $class;

        return $this;
    }

    /**
     * Mark the field as toggleable
     */
    public function toggleable(bool $value = true): static
    {
        $this->toggleable = $value;

        return $this;
    }

    /**
     * Get the field column class
     */
    public function getColClass(ResourceRequest $request): ?string
    {
        $colClass = $this->colClass;

        if ($colClass instanceof Closure) {
            return $colClass($request);
        }

        return $colClass;
    }

    /**
     * Set default value on creation forms
     *
     * @param  mixed  $value
     */
    public function withDefaultValue($value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the field default value
     */
    public function defaultValue(ResourceRequest $request): mixed
    {
        return with($this->value, function ($value) use ($request) {
            if ($value instanceof Closure) {
                return $value($request);
            }

            return $value;
        });
    }

    /**
     * Set collapsible field
     */
    public function collapsed(bool $bool = true): static
    {
        $this->collapsed = $bool;

        return $this;
    }

    /**
     * Set the field display of the help text
     */
    public function helpDisplay(string $display): static
    {
        $this->helpTextDisplay = $display;

        return $this;
    }

    /**
     * Add read only statement
     */
    public function readOnly(bool|callable $value): static
    {
        $this->readOnly = $value;

        return $this;
    }

    /**
     * Determine whether the field is read only
     */
    public function isReadOnly(): bool
    {
        $callback = $this->readOnly;

        return $callback === true || (is_callable($callback) && call_user_func($callback));
    }

    /**
     * Hides the field from the document
     */
    public function displayNone(bool $value = true): static
    {
        $this->displayNone = $value;

        return $this;
    }

    /**
     * Get the component name for the field.
     */
    public function component(): ?string
    {
        return $this->component;
    }

    /**
     * Set the field as primary
     */
    public function primary(bool $bool = true): static
    {
        $this->primary = $bool;

        return $this;
    }

    /**
     * Check whether the field is primary
     */
    public function isPrimary(): bool
    {
        return $this->primary === true;
    }

    /**
     * Set the callback used to determine if the field is required.
     *
     * Useful when you have complex required validation requirements like filled, sometimes etc..,
     * you can manually mark the field as required by passing a boolean when defining the field.
     *
     * This method will only add a "required" indicator to the UI field.
     * You must still define the related requirement rules() that should apply during validation.
     *
     * @param  \Closure|bool  $callback
     */
    public function required($callback = true): static
    {
        $this->isRequiredCallback = $callback;

        return $this;
    }

    /**
     * Check whether the field is required based on the rules
     */
    public function isRequired(ResourceRequest $request): bool
    {
        $callback = $this->isRequiredCallback;

        if ($callback === true || (is_callable($callback) && call_user_func($callback, $request))) {
            return true;
        }

        if (! empty($this->attribute) && is_null($callback)) {
            if ($request->isCreateRequest()) {
                $rules = $this->getCreationRules()[$this->requestAttribute()] ?? [];
            } elseif ($request->isUpdateRequest()) {
                $rules = $this->getUpdateRules()[$this->requestAttribute()] ?? [];
            } elseif (Innoclapps::isImportMapping() || Innoclapps::isImportInProgress()) {
                $rules = $this->getImportRules()[$this->requestAttribute()] ?? [];
            } else {
                $rules = $this->getRules()[$this->requestAttribute()] ?? [];
            }

            return in_array('required', $rules);
        }

        return false;
    }

    /**
     * Check whether the field is unique
     */
    public function isUnique(): bool
    {
        foreach ($this->getRules() as $rules) {
            if (collect($rules)->whereInstanceOf(UniqueResourceRule::class)->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark the field as unique
     */
    public function unique($model, $skipOnImport = true): static
    {
        $this->rules(UniqueResourceRule::make($model)->skipOnImport($skipOnImport));

        return $this;
    }

    /**
     * Mark the field as not unique
     */
    public function notUnique(): static
    {
        foreach ($this->getRules() as $rules) {
            foreach ($rules as $ruleKey => $rule) {
                if ($rule instanceof UniqueResourceRule) {
                    unset($this->rules[$ruleKey]);
                }
            }
        }

        return $this;
    }

    /**
     * Mark that the a unique field can be marked as not unique via settings
     */
    public function canUnmarkUnique(): static
    {
        $this->canUnmarkUnique = true;

        return $this;
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Turahe\Core\Models\Model|null  $model
     * @return \Turahe\Core\Placeholders\GenericPlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        return GenericPlaceholder::make($this->attribute)
            ->description($this->label)
            ->value(function () use ($model) {
                return $this->resolveForDisplay($model);
            });
    }

    /**
     * Provide a callable to prepare the field for validation
     *
     * @param  callable  $callable
     */
    public function prepareForValidation($callable): static
    {
        $this->validationCallback = $callable;

        return $this;
    }

    /**
     * Indicates that the field value should be included in the JSON resource
     * when the user is not authorized to view the model/record
     */
    public function showValueWhenUnauthorizedToView(): static
    {
        $this->alwaysInJsonResource = true;

        return $this;
    }

    /**
     * Indicates whether to emit change event when value is changed
     */
    public function emitChangeEvent(?string $eventName = null): static
    {
        $this->emitChangeEvent = $eventName ?? 'field-'.$this->attribute.'-value-changed';

        return $this;
    }

    /**
     * Set field validation rules for all requests
     */
    public function rules(mixed $rules): static
    {
        $this->rules = array_merge(
            $this->rules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Set field validation rules that are only applied on create request
     */
    public function creationRules(mixed $rules): static
    {
        $this->creationRules = array_merge(
            $this->creationRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Set field validation rules for import
     */
    public function importRules(mixed $rules): static
    {
        $this->importRules = array_merge(
            $this->importRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Get field validation rules for import
     */
    public function getImportRules(): array
    {
        $rules = [
            $this->requestAttribute() => $this->importRules,
        ];

        // We will remove the array rule in case found
        // because the import can handle arrays via coma separated
        return collect(array_merge_recursive(
            $this->getCreationRules(),
            $rules
        ))->reject(fn ($rule) => $rule === 'array')->all();
    }

    /**
     * Set field validation rules that are only applied on update request
     */
    public function updateRules(mixed $rules): static
    {
        $this->updateRules = array_merge(
            $this->updateRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Get field validation rules for the request
     */
    public function getRules(): array
    {
        return $this->createRulesArray($this->rules);
    }

    /**
     * Get the field validation rules for create request
     */
    public function getCreationRules(): array
    {
        $rules = $this->createRulesArray($this->creationRules);

        return array_merge_recursive(
            $this->getRules(),
            $rules
        );
    }

    /**
     * Get the field validation rules for update request
     */
    public function getUpdateRules(): array
    {
        $rules = $this->createRulesArray($this->updateRules);

        return array_merge_recursive(
            $this->getRules(),
            $rules
        );
    }

    /**
     * Create rules ready array
     */
    protected function createRulesArray(array $rules): array
    {
        // If the array is not list, probably the user added array validation
        // rules e.q. '*.key' => 'required', in this case, we will make sure to include them
        if (! array_is_list($rules)) {
            $groups = collect($rules)->mapToGroups(function ($rules, $wildcard) {
                // If the $wildcard is integer, this means that it's a rule for the main field attribute
                // e.q. ['array', '*.key' => 'required']
                return [is_int($wildcard) ? 'attribute' : 'wildcard' => [$wildcard => $rules]];
            })->all();

            $merged = [];

            if (array_key_exists('attribute', $groups)) {
                $merged = array_merge($merged, [$this->requestAttribute() => $groups['attribute']?->flatten()->all()]);
            }

            if (array_key_exists('wildcard', $groups)) {
                $merged = array_merge($merged, $groups['wildcard']->mapWithKeys(function ($rules) {
                    $wildcard = array_key_first($rules);

                    return [$this->requestAttribute().'.'.$wildcard => Arr::wrap($rules[$wildcard])];
                })->all());
            }

            return $merged;
        }

        return [
            $this->requestAttribute() => $rules,
        ];
    }

    /**
     * Set field custom validation error messages
     */
    public function validationMessages(array $messages): static
    {
        $this->validationMessages = $messages;

        return $this;
    }

    /**
     * Get the field validation messages
     */
    public function prepareValidationMessages(): array
    {
        return collect($this->validationMessages)->mapWithKeys(function ($message, $rule) {
            return [$this->requestAttribute().'.'.$rule => $message];
        })->all();
    }

    /**
     * Set whether to exclude the field from Zapier response
     */
    public function excludeFromZapierResponse(): static
    {
        $this->excludeFromZapierResponse = true;

        return $this;
    }

    /**
     * Set the field custom field model
     */
    public function setCustomField(?CustomField $field): static
    {
        $this->customField = $field;

        return $this;
    }

    /**
     * Check whether the current field is a custom field
     */
    public function isCustomField(): bool
    {
        return ! is_null($this->customField);
    }

    /**
     * Get the field request attribute
     *
     * @return string
     */
    public function requestAttribute()
    {
        return $this->requestAttribute ?? $this->attribute;
    }

    /**
     * Create the field attributes for storage for the given request
     *
     * @param  string  $requestAttribute
     */
    public function storageAttributes(ResourceRequest $request, $requestAttribute): array|callable
    {
        if (is_callable($this->saveUsing)) {
            return call_user_func_array($this->saveUsing, [
                $request,
                $requestAttribute,
                $this->attributeFromRequest($request, $requestAttribute),
                $this,
            ]);
        }

        return [
            $this->attribute => $this->attributeFromRequest($request, $requestAttribute),
        ];
    }

    /**
     * Get the field value for the given request
     *
     * @param  string  $requestAttribute
     */
    public function attributeFromRequest(ResourceRequest $request, $requestAttribute): mixed
    {
        return $request->exists($requestAttribute) ? $request[$requestAttribute] : null;
    }

    /**
     * Add custom attributes provider callback when creating/updating
     */
    public function saveUsing(callable $callable): static
    {
        $this->saveUsing = $callable;

        return $this;
    }

    /**
     * Check whether the field is optionable
     */
    public function isOptionable(): bool
    {
        if ($this->isMultiOptionable()) {
            return true;
        }

        return $this instanceof Optionable;
    }

    /**
     * Check whether the field is not optionable
     */
    public function isNotOptionable(): bool
    {
        return ! $this->isOptionable();
    }

    /**
     * Check whether the field is multi optionable
     */
    public function isMultiOptionable(): bool
    {
        return $this instanceof HasMany || $this instanceof MultiSelect || $this instanceof Checkbox;
    }

    /**
     * Check whether the field is not multi optionable
     */
    public function isNotMultiOptionable(): bool
    {
        return ! $this->isMultiOptionable();
    }

    /**
     * Mark the the field should be available only when there is an authRequired user.
     */
    public function authRequired(): static
    {
        $this->authRequired = true;

        return $this;
    }

    /**
     * Resolve the current request instance.
     *
     * @return \Illuminate\Http\Request
     */
    protected function resolveRequest()
    {
        if (Innoclapps::isImportInProgress()) {
            return Import::$currentRequest;
        }

        return app(ResourceRequest::class);
    }

    /**
     * Serialize for front end
     */
    public function jsonSerialize(): array
    {
        // Determine if the field is required and then clear import status when mapping
        $isRequired = $this->isRequired(resolve(ResourceRequest::class));

        if (Innoclapps::isImportMapping()) {
            Innoclapps::setImportStatus(false);
        }

        return array_merge([
            'component' => $this->component(),
            'attribute' => $this->attribute,
            'label' => $this->label,
            'helpText' => $this->helpText,
            'helpTextDisplay' => $this->helpTextDisplay,
            'readonly' => $this->isReadOnly(),
            'supportsInputGroup' => $this->supportsInputGroup(),
            'collapsed' => $this->collapsed,
            'primary' => $this->isPrimary(),
            'icon' => $this->icon,
            'showOnIndex' => $this->showOnIndex,
            'showOnCreation' => $this->showOnCreation,
            'showOnUpdate' => $this->showOnUpdate,
            'showOnDetail' => $this->showOnDetail,
            'applicableForIndex' => $this->isApplicableForIndex(),
            'applicableForCreation' => $this->isApplicableForCreation(),
            'applicableForUpdate' => $this->isApplicableForUpdate(),
            'toggleable' => $this->toggleable,
            'displayNone' => $this->displayNone,
            'emitChangeEvent' => $this->emitChangeEvent,
            'colClass' => $this->getColClass(resolve(ResourceRequest::class)),
            'value' => $this->defaultValue(resolve(ResourceRequest::class)),
            'isRequired' => $isRequired,
            'isUnique' => $this->isUnique(),
            'canUnmarkUnique' => $this->canUnmarkUnique,
            'customField' => $this->isCustomField() ? new CustomFieldResource($this->customField) : null,
        ], $this->meta());
    }
}
