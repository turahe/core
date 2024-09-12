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

namespace Turahe\Core\Tests\Unit;

use Illuminate\Support\Facades\Schema;
use Turahe\Core\Facades\Fields;
use Turahe\Core\Fields\CustomFieldService;
use Turahe\Core\Tests\Concerns\TestsCustomFields;
use Turahe\Core\Tests\TestCase;

class CustomFieldServiceTest extends TestCase
{
    use TestsCustomFields;

    public function test_field_column_is_altered_when_custom_field_is_created()
    {
        $service = new CustomFieldService;

        foreach ($this->fieldsTypesThatRequiresDatabaseColumnCreation() as $type) {
            $field = $service->create([
                'field_type' => $type,
                'field_id' => 'cf_some_id_'.strtolower($type),
                'label' => $type,
                'resource_name' => 'contacts',
                'options' => in_array($type, Fields::getNonOptionableCustomFieldsTypes()) ? [] : ['option-1'],
            ]);

            $this->assertTrue(Schema::hasColumn('contacts', $field->field_id));
        }
    }

    public function test_field_column_is_dropped_when_custom_field_is_deleted()
    {
        $service = new CustomFieldService;

        foreach ($this->fieldsTypesThatRequiresDatabaseColumnCreation() as $type) {
            $field = $service->create([
                'field_type' => $type,
                'field_id' => 'cf_some_id_'.strtolower($type),
                'label' => $type,
                'resource_name' => 'contacts',
                'options' => in_array($type, Fields::getNonOptionableCustomFieldsTypes()) ? [] : ['option-1'],
            ]);

            $service->delete($field->id);
            $this->assertFalse(Schema::hasColumn('contacts', $field->field_id));
        }
    }

    public function test_db_columns_are_not_created_when_multi_optionable_custom_field_is_added()
    {
        $this->signIn();

        $service = new CustomFieldService;

        foreach ($this->fieldsTypesThatDoesntRequiresDatabaseColumnCreation() as $type) {
            $field = $service->create([
                'field_type' => $type,
                'field_id' => 'cf_some_id_'.strtolower($type),
                'label' => $type,
                'resource_name' => 'contacts',
                'options' => ['option-1'],
            ]);

            $this->assertFalse(Schema::hasColumn('contacts', $field->field_id));
        }
    }
}
