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

use Turahe\Core\Table\MorphToManyColumn;

class MorphToMany extends HasMany
{
    /**
     * Provide the column used for index
     */
    public function indexColumn(): MorphToManyColumn
    {
        return tap(new MorphToManyColumn(
            $this->hasManyRelationship,
            $this->labelKey,
            $this->label
        ), function ($column) {
            if ($this->counts()) {
                $column->count()->centered()->sortable();
            }
        });
    }
}
