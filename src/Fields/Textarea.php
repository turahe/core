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
use Turahe\Core\Contracts\Fields\Customfieldable;

class Textarea extends Field implements Customfieldable
{
    /**
     * Field component
     */
    public ?string $component = 'textarea-field';

    /**
     * Textarea rows attribute
     */
    public function rows(string|int $rows): static
    {
        $this->withMeta(['attributes' => ['rows' => $rows]]);

        return $this;
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): ?Column
    {
        $column = parent::indexColumn();

        $column->newlineable = true;

        return $column;
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Turahe\Core\Models\Model|null  $model
     * @return \Turahe\Core\Placeholders\GenericPlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        $placeholder = parent::mailableTemplatePlaceholder($model);

        $placeholder->newlineable = true;

        return $placeholder;
    }

    /**
     * Create the custom field value column in database
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $table
     * @param  string  $fieldId
     * @return void
     */
    public static function createValueColumn($table, $fieldId)
    {
        $table->text($fieldId)->nullable();
    }
}
