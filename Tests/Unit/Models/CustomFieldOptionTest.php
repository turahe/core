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

namespace Turahe\Core\Tests\Unit\Models;

use Turahe\Core\Models\CustomField;
use Turahe\Core\Models\CustomFieldOption;
use Turahe\Core\Tests\TestCase;

class CustomFieldOptionTest extends TestCase
{
    public function test_custom_field_option_has_field()
    {
        $field = $this->makeField();
        $field->save();

        $option = new CustomFieldOption(['name' => 'Option 1', 'display_order' => 1]);
        $field->options()->save($option);

        $this->assertInstanceof(CustomField::class, $option->field);
    }

    protected function makeField($attrs = [])
    {
        return new CustomField(array_merge([
            'field_id' => 'field_id',
            'field_type' => 'Text',
            'resource_name' => 'resource',
            'label' => 'Label',
        ], $attrs));
    }
}
