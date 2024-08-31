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

namespace Turahe\Core\Table;

use JsonSerializable;
use Turahe\Users\Models\User;
use Illuminate\Support\Collection;
use Turahe\Core\Contracts\Metable;
use Illuminate\Contracts\Support\Arrayable;
use Turahe\Core\Filters\UserFiltersService;
use Turahe\Core\Http\Resources\FilterResource;
use Turahe\Core\Table\Exceptions\OrderByNonExistingColumnException;

class TableSettings implements Arrayable, JsonSerializable
{
    /**
     * Holds the partially user meta key for the saved table settings.
     */
    protected string $meta = 'table-settings';

    /**
     * Columns cache.
     */
    protected ?Collection $columns = null;

    /**
     * Create new TableSettings instance.
     */
    public function __construct(protected Table $table, protected Metable $user)
    {
    }

    /**
     * Get the table available actions.
     *
     * The function removes also the actions that are hidden on INDEX
     */
    public function actions(): Collection
    {
        return $this->table->resolveActions($this->table->getRequest())
            ->reject(fn ($action) => $action->hideOnIndex === true)
            ->values();
    }

    /**
     * Get the available table saved filters.
     */
    public function savedFilters(): Collection
    {
        return (new UserFiltersService())->get($this->user->id, $this->table->identifier());
    }

    /**
     * Get the table max height.
     */
    public function maxHeight(): float|int|string|null
    {
        return $this->getCustomizedSettings()['maxHeight'] ?? $this->table->maxHeight;
    }

    /**
     * Check whether the table is condensed.
     */
    public function isCondensed(): bool
    {
        return $this->getCustomizedSettings()['condensed'] ?? false;
    }

    /**
     * Get the table per page.
     */
    public function perPage(): int
    {
        return $this->getCustomizedSettings()['perPage'] ?? $this->table->perPage;
    }

    /**
     * Saves customized table data.
     */
    public function update(?array $data): static
    {
        // Protect the primary fields visibility when
        // direct API request is performed
        if (! empty($data)) {
            $this->guardColumns($data);
        }

        $this->user->setMeta($this->getMetaName(), $data);
        $this->user = User::find($this->user->id);

        return $this;
    }

    /**
     * Get the user columns meta name
     */
    public function getMetaName(): string
    {
        return $this->meta.'-'.$this->table->identifier();
    }

    /**
     * Get table order, checks for custom ordering too
     */
    public function getOrder(): array
    {
        $customizedOrder = $this->getCustomizedOrder();

        if (count($customizedOrder) === 0) {
            return $this->table->order;
        }

        return collect($customizedOrder)->reject(function ($data) {
            // Check and unset the custom ordered field in case no longer exists as available columns
            // For example it can happen a database change and this column is no longer available,
            // for this reason we must not sort by this column because it may be removed from database
            return is_null($this->table->getColumn($data['attribute']));
        })->values()->all();
    }

    /**
     * Validate the order
     *
     * @throws \Turahe\Core\Table\Exceptions\OrderByNonExistingColumnException
     */
    protected function validateOrder(array $order): array
    {
        foreach ($order as $data) {
            throw_if(
                is_null($this->table->getColumn($data['attribute'])),
                new OrderByNonExistingColumnException($data['attribute'])
            );
        }

        return $order;
    }

    /**
     * Get the actual table columns that should be shown to the user
     */
    public function getColumns(): Collection
    {
        return $this->columns ??= $this->retrieveAndConfigureColumns()->sortBy('order')->values();
    }

    /**
     * Retrieve and configure the table columns.
     */
    protected function retrieveAndConfigureColumns(): Collection
    {
        $customizedColumns = collect($this->getCustomizedColumns());
        $availableColumns = $this->table->getColumns()->filter->authorizedToSee()->values();

        // Merge the order and the visibility and all columns so we can filter them later
        return $availableColumns->map(function (Column $column, int $index) use ($customizedColumns) {
            if ($column instanceof ActionColumn) {
                $column->order(1000)->hidden(false);
            } else {
                $customizedColumn = $customizedColumns->firstWhere('attribute', $column->attribute);
                $column->order($customizedColumn ? (int) $customizedColumn['order'] : $index + 1);
                // We will first check if the column has customization applied, if yes, we will
                // use the option from the customization otherwise column is not yet in customized columns, perhaps is added later
                // either set the visibility if defined from the column config or set it default true if not defined
                $column->hidden(
                    $customizedColumn ? ($customizedColumn['hidden'] ?? false) : ($column->hidden ?? false)
                );
            }

            return $column;
        });
    }

    /**
     * Get user customized table data that is stored in database/meta
     */
    public function getCustomizedSettings(): ?array
    {
        return $this->user->getMeta($this->getMetaName());
    }

    /**
     * Get table customized user order
     */
    public function getCustomizedOrder(): array
    {
        return $this->getCustomizedSettings()['order'] ?? [];
    }

    /**
     * Get table customized user columns
     */
    public function getCustomizedColumns(): array
    {
        return $this->getCustomizedSettings()['columns'] ?? [];
    }

    /**
     * Guard the primary and not sortable columns
     */
    protected function guardColumns(array &$payload): void
    {
        $this->guardPrimaryColumns($payload);
        $this->guardNotSortableColumns($payload);
    }

    /**
     * Guards the primary fields from mole changes via API
     */
    protected function guardPrimaryColumns(array &$payload): void
    {
        // Protected the primary fields hidden option
        // when direct API request
        // e.q. the field attribute hidden is set to false when it must be visible
        // because the field is marked as primary field
        foreach ($payload['columns'] as $key => $column) {
            $column = $this->table->getColumn($column['attribute']);

            // Reset with the default attributes for additional protection
            if ($column->isPrimary()) {
                $payload['columns'][$key]['hidden'] = $column->isHidden();
                $payload['columns'][$key]['order'] = $column->order;
            }
        }
    }

    /**
     * Guards the not sortable columns from mole changes via API
     */
    protected function guardNotSortableColumns(array &$payload): void
    {
        // Protected the not sortable columns
        // E.q. if column is marked to be not sortable
        // The user is not allowed to change to sortable
        foreach ($payload['order'] as $key => $sort) {
            $column = $this->table->getColumn($sort['attribute']);

            // Reset with the default attributes for additional protection
            if (! $column->isSortable()) {
                unset($payload['order'][$key]);
            }
        }
    }

    /**
     * Get the saved filters resource
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function filtersResource()
    {
        return FilterResource::collection($this->savedFilters());
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'identifier'             => $this->table->identifier(),
            'perPage'                => $this->perPage(),
            'customizeable'          => $this->table->customizeable,
            'allowDefaultSortChange' => $this->table->allowDefaultSortChange,
            'requestQueryString'     => $this->table->getRequestQueryString(),
            'rules'                  => $this->table->resolveFilters($this->table->getRequest()),
            'maxHeight'              => $this->maxHeight(),
            'condensed'              => $this->isCondensed(),
            'filters'                => $this->filtersResource(),
            'columns'                => $this->getColumns(),
            'order'                  => $this->validateOrder($this->getOrder()),
            'actions'                => $this->actions(),
        ];
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
