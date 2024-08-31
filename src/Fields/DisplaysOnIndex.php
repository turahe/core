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

use Turahe\Core\Table\Column;

trait DisplaysOnIndex
{
    /**
     * @var callable[]
     */
    public array $tapIndexColumnCallbacks = [];

    /**
     * @var callable
     */
    public $indexColumnCallback;

    /**
     * Provide the column used for index
     */
    public function indexColumn(): ?Column
    {
        return new Column($this->attribute, $this->label);
    }

    /**
     * Add custom index column resolver callback
     */
    public function swapIndexColumn(callable $callback): static
    {
        $this->indexColumnCallback = $callback;

        return $this;
    }

    /**
     * Tap the index column
     */
    public function tapIndexColumn(callable $callback): static
    {
        $this->tapIndexColumnCallbacks[] = $callback;

        return $this;
    }

    /**
     * Resolve the index column
     *
     * @return \Turahe\Core\Table\Column|null
     */
    public function resolveIndexColumn()
    {
        /** @var \Turahe\Core\Table\Column */
        $column = is_callable($this->indexColumnCallback) ?
                  call_user_func_array($this->indexColumnCallback, [$this]) :
                  $this->indexColumn();

        if (is_null($column)) {
            return null;
        }

        $column->primary($this->isPrimary());
        $column->help($this->helpText);
        $column->hidden(! $this->showOnIndex);

        foreach ($this->tapIndexColumnCallbacks as $callback) {
            tap($column, $callback);
        }

        return $column;
    }
}
