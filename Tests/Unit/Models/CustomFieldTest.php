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

use Illuminate\Support\Str;
use Turahe\Contacts\Models\Contact;
use Turahe\Core\Fields\Text;
use Turahe\Core\Models\CustomField;
use Turahe\Core\Tests\TestCase;

class CustomFieldTest extends TestCase
{
    public function test_custom_field_has_options()
    {
        $field = $this->makeField(['field_type' => 'Checkbox']);
        $field->save();

        $field->options()->createMany([
            ['name' => 'Option 1', 'display_order' => 1],
            ['name' => 'Option 2', 'display_order' => 2],
        ]);

        $this->assertCount(2, $field->options);
    }

    public function test_custom_field_options_are_sorted_properly()
    {
        $field = $this->makeField(['field_type' => 'Checkbox']);
        $field->save();

        $field->options()->createMany([
            ['name' => 'Option 1', 'display_order' => 2],
            ['name' => 'Option 2', 'display_order' => 1],
        ]);

        $this->assertSame('Option 2', $field->options[0]->name);
        $this->assertSame('Option 1', $field->options[1]->name);
    }

    public function test_custom_field_has_relation_name()
    {
        $field = $this->makeField(['field_type' => 'Checkbox']);

        $this->assertEquals('customField'.Str::studly($field->field_id), $field->relationName);
        $this->assertEquals('customField'.Str::studly($field->field_id), $field->relationName);
    }

    public function test_custom_field_has_field_instance()
    {
        $field = $this->makeField(['field_type' => 'Text']);

        $this->assertInstanceOf(Text::class, $field->instance());
    }

    public function test_can_determine_whether_custom_field_is_multi_optionable()
    {
        $field = $this->makeField(['field_type' => 'Checkbox']);

        $this->assertTrue($field->isMultiOptionable());

        $field = $this->makeField(['field_type' => 'Text']);

        $this->assertFalse($field->isMultiOptionable());
    }

    public function test_can_determine_whether_custom_field_is_not_optionable()
    {
        $field = $this->makeField(['field_type' => 'Text']);

        $this->assertTrue($field->isNotMultiOptionable());

        $field = $this->makeField(['field_type' => 'Checkbox']);

        $this->assertFalse($field->isNotMultiOptionable());
    }

    public function test_can_determine_whether_custom_field_optionable()
    {
        $field = $this->makeField(['field_type' => 'Radio']);

        $this->assertTrue($field->isOptionable());

        $field = $this->makeField(['field_type' => 'Text']);

        $this->assertFalse($field->isOptionable());
    }

    public function test_can_determine_whether_custom_field_not_optionable()
    {
        $field = $this->makeField(['field_type' => 'Radio']);

        $this->assertFalse($field->isNotOptionable());

        $field = $this->makeField(['field_type' => 'Text']);

        $this->assertTrue($field->isNotOptionable());
    }

    public function test_custom_field_options_are_prepared_properly()
    {
        $field = $this->makeField();
        $field->save();

        $field->options()->createMany([
            ['name' => 'Option 1', 'display_order' => 1, 'swatch_color' => '#333333'],
            ['name' => 'Option 2', 'display_order' => 2, 'swatch_color' => '#333332'],
        ]);
        $prepared = $field->prepareOptions();

        $this->assertEquals([
            'id' => $field->options[0]->id,
            'label' => 'Option 1',
            'swatch_color' => '#333333',
        ], $prepared[0]);

        $this->assertEquals([
            'id' => $field->options[1]->id,
            'label' => 'Option 2',
            'swatch_color' => '#333332',
        ], $prepared[1]);
    }

    public function test_custom_field_related_options_are_prepared_properly()
    {
        $field = $this->makeField(['resource_name' => 'contacts', 'field_type' => 'Checkbox']);
        $field->save();
        $field->options()->createMany([
            ['name' => 'Option 1', 'swatch_color' => '#333333', 'display_order' => 1],
            ['name' => 'Option 2', 'display_order' => 2],
        ]);

        $related = Contact::factory()->create();
        $related->{$field->relationName}()->attach([$field->options[0]->id => ['custom_field_id' => $field->id]]);
        $prepared = $field->prepareRelatedOptions($related);

        $this->assertEquals([
            'id' => $field->options[0]->id,
            'label' => 'Option 1',
            'swatch_color' => '#333333',
        ], $prepared[0]);
    }

    protected function makeField($attrs = [])
    {
        return new CustomField(array_merge([
            'field_id' => 'cf_field_id',
            'field_type' => 'Text',
            'resource_name' => 'resource',
            'label' => 'Label',
        ], $attrs));
    }
}
