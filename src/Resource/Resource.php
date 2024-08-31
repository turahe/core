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

namespace Turahe\Core\Resource;

use JsonSerializable;
use Turahe\Core\Table\ID;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Turahe\Core\Facades\Menu;
use Turahe\Core\Fields\Field;
use Turahe\Core\Facades\Cards;
use Turahe\Core\Facades\Fields;
use Turahe\Core\ResolvesActions;
use Turahe\Core\ResolvesFilters;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Settings\SettingsMenu;
use Turahe\Core\Resource\Import\Import;
use Illuminate\Database\Eloquent\Builder;
use Turahe\Core\Criteria\RequestCriteria;
use Turahe\Core\Fields\CustomFieldFactory;
use Turahe\Core\Contracts\Services\Service;
use Turahe\Core\Resource\Import\ImportSample;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Contracts\Resources\Resourceful;
use Turahe\Core\Contracts\Resources\AcceptsUniqueCustomFields;
use Turahe\Core\Contracts\Resources\ResourcefulRequestHandler;

abstract class Resource implements JsonSerializable
{
    use QueriesResources;
    use ResolvesActions;
    use ResolvesFilters;
    use ResolvesTables;

    /**
     * The column the records should be default ordered by when retrieving.
     */
    public static string $orderBy = 'id';

    /**
     * The direction the records should be default ordered by when retrieving.
     */
    public static string $orderByDir = 'asc';

    /**
     * Indicates whether the resource is globally searchable.
     */
    public static bool $globallySearchable = false;

    /**
     * The number of records to query when global searching.
     */
    public int $globalSearchResultsLimit = 5;

    /**
     * Indicates whether the resource fields are customizeable.
     */
    public static bool $fieldsCustomizable = false;

    /**
     * Indicates whether the resource has Zapier hooks.
     */
    public static bool $hasZapierHooks = false;

    /**
     * The model the resource is related to.
     *
     * @var \Turahe\Core\Models\Model|null
     */
    public static string $model;

    /**
     * The underlying model resource instance.
     *
     * @var \Turahe\Core\Models\Model|null
     */
    public $resource;

    protected static array $registered = [];

    /**
     * Record finder instance.
     */
    protected ?RecordFinder $finder = null;

    /**
     * Initialize new Resource class
     */
    public function __construct()
    {
        $this->registerIfNotRegistered();
    }

    /**
     * Get the resource service for CRUD operations.
     */
    public function service(): ?Service
    {
        return null;
    }

    /**
     * Get the resource underlying model class name
     *
     * @return string
     */
    public static function model()
    {
        return static::$model;
    }

    /**
     * Set the resource model instance
     *
     * @param  \Turahe\Core\Models\Model|null  $resource
     */
    public function setModel($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get a fresh instance of the model resource model.
     *
     * @return \Turahe\Core\Models\Model
     */
    public static function newModel()
    {
        $model = static::$model;

        return new $model;
    }

    /**
     * Provide the resource available cards
     */
    public function cards(): array
    {
        return [];
    }

    /**
     *  Get the filters intended for the resource
     *
     * @return \Illuminate\Support\Collection
     */
    public function filtersForResource(ResourceRequest $request)
    {
        return $this->resolveFilters($request)->merge(
            (new CustomFieldFactory($this->name()))->createFieldsForFilters()
        );
    }

    /**
     * Get the actions intended for the resource
     *
     * @return \Illuminate\Support\Collection
     */
    public function actionsForResource(ResourceRequest $request)
    {
        return $this->resolveActions($request);
    }

    /**
     * Get the json resource that should be used for json response
     */
    public function jsonResource(): ?string
    {
        return null;
    }

    /**
     * Create JSON Resource
     *
     * @return mixed
     */
    public function createJsonResource(mixed $data, bool $resolve = false, ?ResourceRequest $request = null)
    {
        $collection = is_countable($data);

        if ($collection) {
            $resource = $this->jsonResource()::collection($data);
        } else {
            $jsonResource = $this->jsonResource();
            $resource = new $jsonResource($data);
        }

        if ($resolve) {
            $request = $request ?: app(ResourceRequest::class)->setResource($this->name());

            if (! $collection) {
                $request->setResourceId($data->getKey());
            }

            return $resource->resolve($request);
        }

        return $resource;
    }

    /**
     * Get the fields that should be included in JSON resource
     *
     * @param  \Turahe\Core\Resource\Http\Request  $request
     * @param  \Turahe\Core\Models\Model  $model
     *
     * Indicates whether the current user can see the model in the JSON resource
     * @param  bool  $canSeeResource
     * @return \Turahe\Core\Fields\FieldsCollection
     */
    public function getFieldsForJsonResource($request, $model, $canSeeResource = true)
    {
        return $this->resolveFields()->reject(function ($field) use ($request) {
            return $field->excludeFromZapierResponse && $request->isZapier();
        })->filter(function (Field $field) use ($canSeeResource) {
            if (! $canSeeResource) {
                return $field->alwaysInJsonResource === true;
            }

            return $canSeeResource;
        })->reject(function ($field) use ($model) {
            return is_null($field->resolveForJsonResource($model));
        })->values();
    }

    /**
     * Provide the available resource fields
     */
    public function fields(Request $request): array
    {
        return [];
    }

    /**
     * Get the resource defined fields
     *
     * @return \Turahe\Core\Fields\FieldsCollection
     */
    public static function getFields()
    {
        return Fields::inGroup(static::name());
    }

    /**
     * Resolve the create fields for resource
     *
     * @return \Turahe\Core\Fields\FieldsCollection
     */
    public function resolveCreateFields()
    {
        return Fields::resolveCreateFields(static::name());
    }

    /**
     * Resolve the update fields for the resource
     *
     * @return \Turahe\Core\Fields\FieldsCollection
     */
    public function resolveUpdateFields()
    {
        return Fields::resolveUpdateFields(static::name());
    }

    /**
     * Resolve the resource fields for display
     *
     * @return \Turahe\Core\Fields\FieldsCollection
     */
    public function resolveFields()
    {
        return static::getFields()->filter->authorizedToSee()->values();
    }

    /**
     * Provide the resource rules available for create and update
     */
    public function rules(ResourceRequest $request): array
    {
        return [];
    }

    /**
     * Provide the resource rules available only for create
     */
    public function createRules(ResourceRequest $request): array
    {
        return [];
    }

    /**
     * Provide the resource rules available only for update
     */
    public function updateRules(ResourceRequest $request): array
    {
        return [];
    }

    /**
     * Provide the criteria that should be used to query only records that the logged-in user is authorized to view
     */
    public function viewAuthorizedRecordsCriteria(): ?string
    {
        return null;
    }

    /**
     * Provide the resource relationship name when it's associated
     */
    public function associateableName(): ?string
    {
        return null;
    }

    /**
     * Provide the menu items for the resource
     */
    public function menu(): array
    {
        return [];
    }

    /**
     * Provide the settings menu items for the resource
     */
    public function settingsMenu(): array
    {
        return [];
    }

    /**
     * Register permissions for the resource
     */
    public function registerPermissions(): void
    {
    }

    /**
     * Get the custom validation messages for the resource
     * Useful for resources without fields.
     */
    public function validationMessages(): array
    {
        return [];
    }

    /**
     * Determine whether the resource has associations
     */
    public function isAssociateable(): bool
    {
        return ! is_null($this->associateableName());
    }

    /**
     * Get the resource available associative resources
     *
     * @return \Illuminate\Support\Collection
     */
    public function availableAssociations()
    {
        return Innoclapps::registeredResources()
            ->reject(fn ($resource) => is_null($resource->associateableName()))
            ->filter(fn ($resource) => static::newModel()->isRelation($resource->associateableName()))
            ->values();
    }

    /**
     * Check whether the given resource can be associated to the current resource
     */
    public function canBeAssociated(string $resourceName): bool
    {
        return (bool) $this->availableAssociations()->first(
            fn ($resource) => $resource->name() == $resourceName
        );
    }

    /**
     * Get the resourceful CRUD handler class.
     */
    public function resourcefulHandler(ResourceRequest $request): ResourcefulRequestHandler|ResourcefulHandlerWithFields
    {
        return count($this->fields($request)) > 0 ?
            new ResourcefulHandlerWithFields($request) :
            new ResourcefulHandler($request);
    }

    /**
     * Determine if this resource is searchable
     */
    public static function searchable(): bool
    {
        return ! empty(static::newModel()->getSearchableColumns());
    }

    /**
     * Get the displayable label of the resource
     */
    public static function label(): string
    {
        return Str::plural(Str::title(Str::snake(class_basename(get_called_class()), ' ')));
    }

    /**
     * Get the displayable singular label of the resource
     */
    public static function singularLabel(): string
    {
        return Str::singular(static::label());
    }

    /**
     * Get the internal name of the resource
     */
    public static function name(): string
    {
        return Str::plural(Str::kebab(class_basename(get_called_class())));
    }

    /**
     * Get the internal singular name of the resource
     */
    public static function singularName(): string
    {
        return Str::singular(static::name());
    }

    /**
     * Get the resource importable class
     */
    public function importable(): Import
    {
        return new Import($this);
    }

    /**
     * Get the resource import sample class
     */
    public function importSample(): ImportSample
    {
        return new ImportSample($this);
    }

    /**
     * Get the resource export class.
     */
    public function exportable(Builder $query): Export
    {
        return new Export($this, $query);
    }

    /**
     * Create ID field instance for the resource.
     */
    public function idField()
    {
        return ID::make(__('core::app.id'), $this->newModel()->getKeyName());
    }

    /**
     * Register the resource available menu items
     */
    protected function registerMenuItems(): void
    {
        foreach ($this->menu() as $item) {
            if (! $item->singularName) {
                $item->singularName($this->singularLabel());
            }

            Menu::register($item);
        }
    }

    /**
     * Register the resource settings menu items
     */
    protected function registerSettingsMenuItems(): void
    {
        foreach ($this->settingsMenu() as $key => $item) {
            SettingsMenu::register($item, is_int($key) ? $this->name() : $key);
        }
    }

    /**
     * Register the resource available cards
     */
    protected function registerCards(): void
    {
        Cards::register($this->name(), $this->cards(...));
    }

    /**
     * Register the resource available CRUD fields
     */
    protected function registerFields(): void
    {
        Fields::group($this->name(), fn () => $this->fields(request()));
    }

    /**
     * Register common permissions for the resource
     */
    protected function registerCommonPermissions(): void
    {
        if ($callable = config('core.resources.permissions.common')) {
            (new $callable)($this);
        }
    }

    /**
     * Get the record finder instance
     */
    public function finder(): RecordFinder
    {
        return $this->finder ??= new RecordFinder($this->newModel());
    }

    /**
     * Clear the registered resource.
     */
    public static function clearRegisteredResources(): void
    {
        static::$registered = [];
    }

    /**
     * Register the resource if not registered.
     */
    protected function registerIfNotRegistered(): void
    {
        if (! isset(static::$registered[static::class])) {
            $this->register();

            static::$registered[static::class] = true;
        }
    }

    /**
     * Register the resource information
     */
    protected function register(): void
    {
        $this->registerPermissions();
        $this->registerCards();

        if ($this instanceof Resourceful) {
            $this->registerFields();
        }

        Innoclapps::booting(function () {
            $this->registerMenuItems();
            $this->registerSettingsMenuItems();
        });
    }

    /**
     * Get the request criteria for the resource.
     */
    public function getRequestCriteria(ResourceRequest $request): RequestCriteria
    {
        return new RequestCriteria($request);
    }

    /**
     * Serialize the resource
     */
    public function jsonSerialize(): array
    {
        return [
            'name'                      => $this->name(),
            'singularName'              => $this->singularName(),
            'label'                     => $this->label(),
            'singularLabel'             => $this->singularLabel(),
            'fieldsCustomizable'        => static::$fieldsCustomizable,
            'acceptsUniqueCustomFields' => $this instanceof AcceptsUniqueCustomFields,
        ];
    }
}
