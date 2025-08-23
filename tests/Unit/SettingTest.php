<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Illuminate\Database\Eloquent\Builder;
use Turahe\Core\Models\Setting;
use Turahe\Core\Tests\TestCase;

class SettingTest extends TestCase
{
    public function test_setting_uses_configurable_primary_key(): void
    {
        $setting = new Setting;

        // Test that the model has the configurable primary key trait methods
        $this->assertTrue(method_exists($setting, 'shouldUseUniqueIds'));
        $this->assertTrue(method_exists($setting, 'getConfiguredKeyType'));
        $this->assertTrue(method_exists($setting, 'shouldUseIncrementing'));
        $this->assertTrue(method_exists($setting, 'newUniqueId'));
        $this->assertTrue(method_exists($setting, 'uniqueIds'));
    }

    public function test_setting_primary_key_configuration_defaults_to_ulid(): void
    {
        $setting = new Setting;

        // Default configuration should be ULID
        $this->assertTrue($setting->shouldUseUniqueIds());
        $this->assertEquals('string', $setting->getConfiguredKeyType());
        $this->assertFalse($setting->shouldUseIncrementing());
        $this->assertEquals('ulid', $setting->getPrimaryKeyType());
    }

    public function test_setting_primary_key_configuration_with_bigincrements(): void
    {
        // Set configuration to bigincrements
        config(['userstamps.users_table_column_type' => 'bigincrements']);

        $setting = new Setting;

        $this->assertFalse($setting->shouldUseUniqueIds());
        $this->assertEquals('int', $setting->getConfiguredKeyType());
        $this->assertTrue($setting->shouldUseIncrementing());
        $this->assertEquals('bigincrements', $setting->getPrimaryKeyType());
    }

    public function test_setting_primary_key_configuration_with_ulid(): void
    {
        // Set configuration to ulid
        config(['userstamps.users_table_column_type' => 'ulid']);

        $setting = new Setting;

        $this->assertTrue($setting->shouldUseUniqueIds());
        $this->assertEquals('string', $setting->getConfiguredKeyType());
        $this->assertFalse($setting->shouldUseIncrementing());
        $this->assertEquals('ulid', $setting->getPrimaryKeyType());
    }

    public function test_setting_primary_key_configuration_with_uuid(): void
    {
        // Set configuration to uuid
        config(['userstamps.users_table_column_type' => 'uuid']);

        $setting = new Setting;

        $this->assertTrue($setting->shouldUseUniqueIds());
        $this->assertEquals('string', $setting->getConfiguredKeyType());
        $this->assertFalse($setting->shouldUseIncrementing());
        $this->assertEquals('uuid', $setting->getPrimaryKeyType());
    }

    public function test_setting_uses_user_stamps(): void
    {
        $setting = new Setting;

        $this->assertTrue(method_exists($setting, 'author'));
        $this->assertTrue(method_exists($setting, 'editor'));
        $this->assertTrue(method_exists($setting, 'destroyer'));
    }

    public function test_setting_uses_soft_deletes(): void
    {
        $setting = new Setting;

        $this->assertTrue(method_exists($setting, 'trashed'));
        $this->assertTrue(method_exists($setting, 'restore'));
        $this->assertTrue(method_exists($setting, 'forceDelete'));
    }

    public function test_setting_table_is_configurable(): void
    {
        $setting = new Setting;

        $this->assertEquals(config('core.tables.settings'), $setting->getTable());
    }

    public function test_setting_has_fillable_attributes(): void
    {
        $setting = new Setting;

        $expectedFillable = [
            'model_id',
            'model_type',
            'key',
            'value',
            'group',
        ];

        $this->assertEquals($expectedFillable, $setting->getFillable());
    }

    public function test_setting_scope_group_returns_builder(): void
    {
        $query = Setting::query();
        $result = $query->group('preferences');

        $this->assertInstanceOf(Builder::class, $result);
    }

    public function test_setting_scope_group_applies_where_clause(): void
    {
        // Test that the scope applies the correct where clause
        $query = Setting::group('preferences');
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        $this->assertStringContainsString('where', $sql);
        $this->assertStringContainsString('group', $sql);
        $this->assertContains('preferences', $bindings);
    }

    public function test_setting_generates_unique_ids_when_configured_for_ulid(): void
    {
        config(['userstamps.users_table_column_type' => 'ulid']);

        $setting = new Setting;

        $this->assertTrue(method_exists($setting, 'newUniqueId'));
        $this->assertNotEmpty($setting->newUniqueId());
        $this->assertEquals(['id'], $setting->uniqueIds());
    }

    public function test_setting_generates_unique_ids_when_configured_for_uuid(): void
    {
        config(['userstamps.users_table_column_type' => 'uuid']);

        $setting = new Setting;

        $this->assertTrue(method_exists($setting, 'newUniqueId'));
        $this->assertNotEmpty($setting->newUniqueId());
        $this->assertEquals(['id'], $setting->uniqueIds());
    }

    public function test_setting_does_not_generate_unique_ids_when_configured_for_bigincrements(): void
    {
        config(['userstamps.users_table_column_type' => 'bigincrements']);

        $setting = new Setting;

        $this->assertTrue(method_exists($setting, 'newUniqueId'));
        $this->assertEmpty($setting->uniqueIds());
    }
}
