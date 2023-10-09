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

namespace Modules\Core\Rules;

class UniqueResourceRule extends UniqueRule
{
    /**
     * Indicates whether to exclude the unique validation from import.
     */
    public bool $skipOnImport = false;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $modelName, string|int|null $ignore = 'resourceId', ?string $column = 'NULL')
    {
        parent::__construct($modelName, $ignore, $column);
    }

    /**
     * Set whether the exclude this validation rule from import.
     */
    public function skipOnImport(bool $value): static
    {
        $this->skipOnImport = $value;

        return $this;
    }
}
