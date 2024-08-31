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

use Closure;
use JsonSerializable;
use Turahe\Core\Makeable;
use Turahe\Core\HasHelpText;
use Turahe\Core\Authorizeable;
use Turahe\Core\MetableElement;
use Turahe\Core\Contracts\Countable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Contracts\Support\Arrayable;

class Column implements Arrayable, JsonSerializable
{
    use Authorizeable, HasHelpText, Makeable, MetableElement;

    /**
     * Attributes to append with the response.
     */
    public array $appends = [];

    /**
     * Additional fields to select.
     */
    public array $select = [];

    /**
     * Custom query for this field.
     */
    public Expression|Closure|string|null $queryAs = null;

    /**
     * Indicates whether the column is sortable.
     */
    public bool $sortable = true;

    /**
     * Indicates whether to add the column contents in the front end as HTML.
     */
    public bool $asHtml = false;

    /**
     * Indicates whether the column is hidden.
     */
    public ?bool $hidden = null;

    /**
     * Indicates special columns and some actions are disabled.
     */
    public bool $primary = false;

    /**
     * Table th/td min width.
     */
    public ?string $minWidth = '200px';

    /**
     * The column default order.
     */
    public ?int $order = null;

    /**
     * Indicates whether to include the column in the query when it's hidden.
     */
    public bool $queryWhenHidden = false;

    /**
     * Custom column display formatter.
     */
    public ?Closure $displayAs = null;

    /**
     * Data heading component.
     */
    public string $component = 'table-data-column';

    /**
     * @var callable|null
     */
    public $orderByUsing;

    /**
     * Indicates whether the column can be customized.
     */
    public bool $customizeable = true;

    /**
     * Indicates whether the column may contain new lines.
     */
    public bool $newlineable = false;

    /**
     * Initialize new Column instance.
     */
    public function __construct(public string $attribute, public ?string $label = null)
    {
    }

    /**
     * Custom query for this column.
     */
    public function queryAs(Expression|Closure|string $queryAs): static
    {
        $this->queryAs = $queryAs;

        return $this;
    }

    /**
     * Set column name/label.
     */
    public function label(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set whether the column is sortable.
     */
    public function sortable(bool $bool = true): static
    {
        $this->sortable = $bool;

        return $this;
    }

    /**
     * Check whether the column is sortable.
     */
    public function isSortable(): bool
    {
        return $this->sortable === true;
    }

    /**
     * Add the column content as HTML.
     */
    public function asHtml(bool $bool = true): static
    {
        $this->asHtml = $bool;

        return $this;
    }

    /**
     * Set column visibility.
     */
    public function hidden(bool $bool = true): static
    {
        $this->hidden = $bool;

        return $this;
    }

    /**
     * Check whether the column is hidden.
     */
    public function isHidden(): bool
    {
        return $this->hidden === true;
    }

    /**
     * Mark the column as primary.
     */
    public function primary(bool $bool = true): static
    {
        $this->primary = $bool;

        return $this;
    }

    /**
     * Check whether the column is primary.
     */
    public function isPrimary(): bool
    {
        return $this->primary === true;
    }

    /**
     * Set column td custom class.
     */
    public function tdClass(string $class): static
    {
        return $this->withMeta([__FUNCTION__ => $class]);
    }

    /**
     * Set column th custom class.
     */
    public function thClass(string $class): static
    {
        return $this->withMeta([__FUNCTION__ => $class]);
    }

    /**
     * Set column th/td width.
     */
    public function width($width): static
    {
        return $this->withMeta([__FUNCTION__ => $width]);
    }

    /**
     * Set column th/td min width.
     */
    public function minWidth(?string $minWidth): static
    {
        $this->minWidth = $minWidth;

        return $this;
    }

    /**
     * Center the column heading and data.
     */
    public function centered(bool $value = true): static
    {
        return $this->withMeta([__FUNCTION__ => $value]);
    }

    /**
     * Set the column default order.
     */
    public function order(int $order): static
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Whether to select/query the column when the column hidden.
     */
    public function queryWhenHidden(bool $bool = true): static
    {
        $this->queryWhenHidden = $bool;

        return $this;
    }

    /**
     * Custom column formatter.
     */
    public function displayAs(Closure $callback): static
    {
        $this->displayAs = $callback;

        return $this;
    }

    /**
     * Add additional fields to be selected when querying the column.
     */
    public function select(array|string $fields): static
    {
        $this->select = array_merge(
            $this->select,
            (array) $fields
        );

        return $this;
    }

    /**
     * Set attributes to appends in the model.
     */
    public function appends(array|string $attributes): static
    {
        $this->appends = array_merge(
            $this->appends,
            (array) $attributes
        );

        return $this;
    }

    /**
     * Check whether a column can count relations.
     */
    public function isCountable(): bool
    {
        return $this instanceof Countable;
    }

    /**
     * Check whether the column is a relation.
     */
    public function isRelation(): bool
    {
        return $this instanceof RelationshipColumn;
    }

    /**
     * Get the column data component.
     */
    public function component(): string
    {
        return $this->component;
    }

    /**
     * Set the column data component for the front-end.
     */
    public function useComponent(string $component): static
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Apply the order by query for the column.
     */
    public function orderBy(Builder $query, array $order): Builder
    {
        if (! $this->sortable) {
            return $query;
        }

        ['attribute' => $attribute, 'direction' => $direction] = $order;

        if (is_callable($this->orderByUsing)) {
            return call_user_func_array($this->orderByUsing, [$query, $attribute, $direction, $this]);
        }

        return $query->orderBy($attribute, $direction);
    }

    /**
     * Order by using custom callback.
     */
    public function orderByUsing(callable $callback): static
    {
        $this->orderByUsing = $callback;

        return $this;
    }

    /**
     * Set whether the column can be customized.
     */
    public function customizeable(bool $value = true)
    {
        $this->customizeable = $value;

        return $this;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge([
            'attribute'     => $this->attribute,
            'label'         => $this->label,
            'sortable'      => $this->isSortable(),
            'asHtml'        => $this->asHtml,
            'hidden'        => $this->isHidden(),
            'primary'       => $this->isPrimary(),
            'component'     => $this->component(),
            'minWidth'      => $this->minWidth,
            'order'         => $this->order,
            'helpText'      => $this->helpText,
            'customizeable' => $this->customizeable,
            'newlineable'   => $this->newlineable,
            // 'isCountable' => $this->isCountable(),
        ], $this->meta());
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
