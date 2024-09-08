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

namespace Turahe\Core\Tests\Feature\Fields;

use Tests\TestCase;
use Turahe\Core\Facades\Fields;
use Turahe\Core\Fields\Text;

class FieldControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_fields_endpoints()
    {
        $this->getJson('/api/fields/FAKE_GROUP/FAKE_VIEW')->assertUnauthorized();
        $this->postJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW')->assertUnauthorized();
        $this->getJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW')->assertUnauthorized();
        $this->deleteJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW/reset')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_fields_endpoints()
    {
        $this->asRegularUser()->signIn();

        $this->postJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW')->assertForbidden();
        $this->getJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW')->assertForbidden();
        $this->deleteJson('/api/fields/settings/FAKE_GROUP/FAKE_VIEW/reset')->assertForbidden();
    }

    public function test_fields_can_be_saved()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'test')->collapsed(),
                Text::make('test_field_2', 'test')->collapsed(),
            ];
        });

        $this->postJson('/api/fields/settings/testing/'.Fields::CREATE_VIEW, $attributes = [
            'test_field_1' => ['order' => 1],
            'test_field_2' => ['order' => 2],
        ])->assertOk();

        $this->assertEquals(
            $attributes['test_field_1']['order'],
            Fields::customized('testing', Fields::CREATE_VIEW)['test_field_1']['order']
        );

        $this->assertEquals(
            $attributes['test_field_2']['order'],
            Fields::customized('testing', Fields::CREATE_VIEW)['test_field_2']['order']
        );
    }

    public function test_fields_can_be_resetted()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'test')->collapsed(),
                Text::make('test_field_2', 'test')->collapsed(),
            ];
        });

        $this->postJson('/api/fields/settings/testing/'.Fields::CREATE_VIEW, $attributes = [
            'test_field_1' => ['order' => 1],
            'test_field_2' => ['order' => 2],
        ]);

        $this->deleteJson('/api/fields/settings/testing/'.Fields::CREATE_VIEW.'/reset')->assertOk();

        $this->assertCount(0, Fields::customized('testing', Fields::CREATE_VIEW));
    }

    public function test_only_creation_fields_can_be_retrieved()
    {
        $this->signIn();

        $fields = [
            Text::make('test_field_2', 'test')->hideWhenCreating(),
            Text::make('test_field_1', 'test'),
        ];

        Fields::group('testing', $fields);

        $fields = collect($fields);

        $this->getJson('/api/fields/testing/'.Fields::CREATE_VIEW)
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.attribute', $fields->get(1)->attribute);
    }

    public function test_only_update_fields_can_be_retrived()
    {
        $this->signIn();

        $fields = [
            Text::make('test_field_2', 'test')->hideWhenUpdating(),
            Text::make('test_field_1', 'test'),
        ];

        Fields::group('testing', $fields);

        $fields = collect($fields);

        $this->getJson('/api/fields/testing/'.Fields::UPDATE_VIEW)
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.attribute', $fields->get(1)->attribute);
    }

    public function test_unauthorized_user_cannot_see_fields_that_is_not_allowed_to_see()
    {
        $this->asRegularUser()->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test', 'test'),
                Text::make('test', 'test')->canSeeWhen('DUMMY_ABILITY', 'DUMMY_MODEL'),
                Text::make('test', 'test')->canSee(function () {
                    return false;
                }),
            ];
        });

        $this->getJson('/api/fields/testing/'.Fields::CREATE_VIEW)->assertJsonCount(1);
    }

    public function test_super_admin_can_see_all_fields_that_are_authorized_via_gate()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test', 'test')->canSeeWhen('DUMMY_ABILITY', 'DUMMY_MODEL'),
                Text::make('test', 'test')->canSeeWhen('DUMMY_ABILITY', 'DUMMY_MODEL'),
            ];
        });

        $this->getJson('/api/fields/testing/'.Fields::CREATE_VIEW)->assertJsonCount(2);
    }

    public function test_super_admin_cannot_see_fields_that_are_authorized_via_closure_by_returning_false()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test', 'test'),
                Text::make('test', 'test')->canSee(function () {
                    // If returned false directly and the check is not
                    // performed via gate, it should not be visible to super
                    // admin either
                    return false;
                }),
            ];
        });

        $this->getJson('/api/fields/testing/'.Fields::CREATE_VIEW)->assertJsonCount(1);
    }

    public function test_cache_customized_fields_cache_is_cleared_after_update()
    {
        $this->signIn();

        Fields::group('testing', function () {
            return [
                Text::make('test_field_1', 'test')->collapsed(),
                Text::make('test_field_2', 'test')->collapsed(),
            ];
        });

        Fields::customize([
            'test_field_1' => ['order' => 2],
            'test_field_2' => ['order' => 1],
        ], 'testing', Fields::CREATE_VIEW);

        $fieldsNow = Fields::resolve('testing', Fields::CREATE_VIEW);

        $this->assertEquals(1, $fieldsNow[0]->order);
        $this->assertEquals('test_field_2', $fieldsNow[0]->attribute);
    }
}
