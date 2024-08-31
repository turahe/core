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

namespace Turahe\Core\Charts;

use Turahe\Core\Card\Card;
use Illuminate\Http\Request;

abstract class Chart extends Card
{
    /**
     * Indicates whether the chart values are amount
     */
    protected bool $amountValue = false;

    /**
     * Chart color/variant class
     */
    protected ?string $color = null;

    /**
     * The method to perform the line chart calculations
     *
     * @return mixed
     */
    abstract public function calculate(Request $request);

    /**
     * The chart available labels
     */
    public function labels($result): array
    {
        return [];
    }

    /**
     * Set chart color
     */
    public function color(string $color): static
    {
        $this->color = 'chart-'.$color;

        return $this;
    }

    /**
     * Prepate the data for the front-end
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'result'       => $this->calculate(request()),
            'amount_value' => $this->amountValue,
            'color'        => $this->color,
        ]);
    }
}
