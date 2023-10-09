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

namespace Modules\Core\Filters;

use Exception;
use JsonSerializable;
use Modules\Core\Makeable;
use Modules\Core\Fields\ChangesKeys;

class Operand implements JsonSerializable
{
    use ChangesKeys, Makeable;

    /**
     * @var \Modules\Core\Filters\Filter
     */
    public $rule;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var string
     */
    public $label;

    /**
     * Initialize Operand class
     *
     * @param  mixed  $value
     * @param  string  $label
     */
    public function __construct($value, $label)
    {
        $this->value = $value;
        $this->label = $label;
    }

    /**
     * Set the operand filter
     *
     * @param  \Modules\Core\Filters\Fitler|string  $rule
     * @return \Modules\Core\Filters\Operand
     */
    public function filter($rule)
    {
        if (is_string($rule)) {
            $rule = $rule::make($this->value);
        }

        if ($rule instanceof HasMany) {
            throw new Exception('Cannot use HasMany filter in operands');
        }

        $this->rule = $rule;

        return $this;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return [
            'value'    => $this->value,
            'label'    => $this->label,
            'valueKey' => $this->valueKey,
            'labelKey' => $this->labelKey,
            'rule'     => $this->rule,
        ];
    }
}
