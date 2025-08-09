<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Turahe\Core\Models\Setting;
use Turahe\Core\Tests\Models\User;
use Turahe\Core\Tests\TestCase;

class HasSettingsTest extends TestCase
{
    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testModel = User::create([]);
    }

    public function test_provides_a_settings_relation(): void
    {
        $this->assertInstanceOf(MorphMany::class, $this->testModel->settings());
        $this->assertInstanceOf(Collection::class, $this->testModel->settings);
    }

    public function test_can_model_get_rules(): void
    {
        $this->assertIsArray($this->testModel->getRules());
    }

    public function test_can_model_get_default_settings(): void
    {
        $this->assertIsArray($this->testModel->getDefaultSettings());
    }

    public function test_can_model_has_relation_with_model_settings(): void
    {

        $setting = [
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
            'datetime' => 'Y-m-d H:i:s',
        ];
        $userSettings = $this->testModel->setSetting($setting);

        $this->assertInstanceOf(User::class, $userSettings);
        $this->assertDatabaseHas('settings', [
            'model_type' => $this->testModel->getMorphClass(),
            'model_id' => $this->testModel->getKey(),
            'key' => 'datetime',
            'value' => $setting['datetime'],
        ]);
        $this->assertDatabaseHas('settings', [
            'model_type' => $this->testModel->getMorphClass(),
            'model_id' => $this->testModel->getKey(),
            'key' => 'language',
            'value' => $setting['language'],
        ]);
        $this->assertDatabaseHas('settings', [
            'model_type' => $this->testModel->getMorphClass(),
            'model_id' => $this->testModel->getKey(),
            'key' => 'timezone',
            'value' => $setting['timezone'],
        ]);
        $this->assertIsArray($this->testModel->allSetting());
        $this->assertEquals($setting, $this->testModel->allSetting());
        $this->assertInstanceOf(MorphMany::class, $this->testModel->settings());

    }

    public function test_can_model_exist_settings(): void
    {

        $setting = [
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
            'datetime' => 'Y-m-d H:i:s',
        ];
        $this->testModel->setSetting($setting);

        $this->assertTrue($this->testModel->existSetting());

    }

    public function test_can_model_empty_settings(): void
    {

        $this->assertTrue($this->testModel->emptySetting());

    }

    public function test_can_model_has_settings(): void
    {

        $setting = [
            'language' => 'id',
        ];
        $this->testModel->setSetting($setting);
        $this->assertTrue($this->testModel->hasSetting('language'));

    }

    public function test_can_model_delete_settings(): void
    {

        $setting = [
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
        ];
        $this->testModel->setSetting($setting);
        $deleted = $this->testModel->deleteSetting('language');

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('settings', [
            'model_type' => $this->testModel->getMorphClass(),
            'model_id' => $this->testModel->getKey(),
            'key' => 'language',
            'value' => 'id',
        ]);
        $this->assertDatabaseHas('settings', [
            'model_type' => $this->testModel->getMorphClass(),
            'model_id' => $this->testModel->getKey(),
            'key' => 'timezone',
            'value' => 'Asia/Jakarta',
        ]);

    }

    public function test_can_model_clear_settings(): void
    {

        $setting = [
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
        ];
        $this->testModel->setSetting($setting);
        $deleted = $this->testModel->clear();

        $this->assertTrue($deleted);
        $this->assertSoftDeleted('settings', [
            'model_type' => $this->testModel->getMorphClass(),
            'model_id' => $this->testModel->getKey(),
            'key' => 'language',
            'value' => 'id',
        ]);
        $this->assertSoftDeleted('settings', [
            'model_type' => $this->testModel->getMorphClass(),
            'model_id' => $this->testModel->getKey(),
            'key' => 'timezone',
            'value' => 'Asia/Jakarta',
        ]);

    }

    public function test_can_model_get_setting_value(): void
    {

        $setting = [
            'language' => 'id',
        ];
        $this->testModel->setSetting($setting);
        $this->assertEquals($setting['language'], $this->testModel->getSettingsValue('language'));

    }

    public function test_can_model_update_settings(): void
    {
        $setting = [
            'timezone' => 'Asia/Jakarta',
        ];
        $this->testModel->setSetting($setting);
        $userSettings = $this->testModel->updateSetting('timezone', 'UTC');

        $this->assertInstanceOf(User::class, $userSettings);
        $this->assertDatabaseHas('settings', [
            'model_type' => $this->testModel->getMorphClass(),
            'model_id' => $this->testModel->getKey(),
            'key' => 'timezone',
            'value' => 'UTC',
        ]);
        $this->assertInstanceOf(MorphMany::class, $this->testModel->settings());
    }

    public function test_can_model_delete_and_all_settings(): void
    {
        $setting = [
            'language' => 'id',
            'timezone' => 'Asia/Jakarta',
            'datetime' => 'Y-m-d H:i:s',
        ];
        $this->testModel->setSetting($setting);

        $deleted = $this->testModel->delete();
        $this->assertTrue($deleted);

        $this->assertSoftDeleted('settings',
            [
                'model_type' => $this->testModel->getMorphClass(),
                'model_id' => $this->testModel->getKey(),
            ]);

        Setting::withTrashed()->get()->each(function (Setting $setting) {
            $this->assertEquals($this->testModel->getMorphClass(), $setting->model_type);
            $this->assertEquals($this->testModel->getKey(), $setting->model_id);
            $this->assertNotNull($setting->deleted_at);
        });

        $this->assertDatabaseMissing('users', ['id' => $this->testModel->getKey()]);
    }

    // ===============================================
    // Tests for PHP 8.4 Features
    // ===============================================

    public function test_rules_readonly_property(): void
    {
        // Test model without settingsRules property
        $rules = $this->testModel->rules;
        $this->assertIsArray($rules);
        $this->assertEmpty($rules);
        
        // Test model with settingsRules property
        $modelWithRules = new class extends \Turahe\Core\Tests\Models\User {
            use \Turahe\Core\Concerns\HasSettings;
            protected array $settingsRules = ['email' => 'required|email'];
        };
        
        $rulesWithData = $modelWithRules->rules;
        $this->assertIsArray($rulesWithData);
        $this->assertEquals(['email' => 'required|email'], $rulesWithData);
    }

    public function test_default_settings_hooked_property(): void
    {
        // Test model without defaultSettings property
        $defaults = $this->testModel->defaultSettingsHooked;
        $this->assertIsArray($defaults);
        $this->assertNotEmpty($defaults); // User model has defaultSettings
        
        // Test model with defaultSettings property
        $modelWithDefaults = new class extends \Turahe\Core\Tests\Models\User {
            use \Turahe\Core\Concerns\HasSettings;
            protected array $defaultSettings = ['theme' => 'dark', 'lang' => 'en'];
        };
        
        $defaultsWithData = $modelWithDefaults->defaultSettingsHooked;
        $this->assertIsArray($defaultsWithData);
        $this->assertEquals(['theme' => 'dark', 'lang' => 'en'], $defaultsWithData);
    }

    public function test_settings_count_readonly_property(): void
    {
        // Initially no settings
        $this->assertEquals(0, $this->testModel->settingsCount);
        
        // Add settings
        $this->testModel->setSetting([
            'theme' => 'dark',
            'language' => 'en',
            'notifications' => 'enabled'
        ]);
        
        // Refresh model
        $this->testModel->refresh();
        $this->assertEquals(3, $this->testModel->settingsCount);
    }

    public function test_has_settings_attached_readonly_property(): void
    {
        // Initially no settings
        $this->assertFalse($this->testModel->hasSettingsAttached);
        
        // Add setting
        $this->testModel->setSetting(['theme' => 'dark']);
        
        // Refresh model
        $this->testModel->refresh();
        $this->assertTrue($this->testModel->hasSettingsAttached);
    }

    public function test_all_settings_collection_readonly_property(): void
    {
        // Add settings
        $this->testModel->setSetting([
            'theme' => 'dark',
            'language' => 'en'
        ]);
        
        $collection = $this->testModel->allSettingsCollection;
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertTrue($collection->has('theme'));
        $this->assertTrue($collection->has('language'));
    }

    public function test_is_cache_enabled_readonly_property(): void
    {
        // Test default cache setting
        $isCacheEnabled = $this->testModel->isCacheEnabled;
        $this->assertIsBool($isCacheEnabled);
        
        // Should match config value
        $this->assertEquals(config('core.cache.enabled', true), $isCacheEnabled);
    }

    public function test_set_setting_with_array_spread_performance(): void
    {
        // Test setting with various data types
        $settings = [
            'string_setting' => 'value',
            'array_setting' => ['key1' => 'value1', 'key2' => 'value2'],
            'boolean_setting' => true,
            'numeric_setting' => 123
        ];
        
        $result = $this->testModel->setSetting($settings, 'performance_group');
        
        $this->assertInstanceOf(\Turahe\Core\Tests\Models\User::class, $result);
        $this->assertEquals(4, $this->testModel->settings()->count());
        
        // Verify each setting was stored correctly
        foreach ($settings as $key => $value) {
            $setting = $this->testModel->settings()->where('key', $key)->first();
            $this->assertNotNull($setting);
            $this->assertEquals('performance_group', $setting->group);
        }
    }

    public function test_get_cache_instance_lazy_initialization(): void
    {
        // This test verifies that the cache instance is created on demand
        // We can't directly access the private property, but we can test the behavior
        
        // Add a setting to trigger cache operations
        $this->testModel->setSetting(['test_key' => 'test_value']);
        
        // Get setting to trigger cache usage
        $setting = $this->testModel->getSetting('test_key');
        
        $this->assertNotNull($setting);
        $this->assertEquals('test_value', $setting->value);
    }

    public function test_validate_with_enhanced_error_handling(): void
    {
        // Create model with validation rules
        $modelWithRules = new class extends \Turahe\Core\Tests\Models\User {
            use \Turahe\Core\Concerns\HasSettings;
            protected array $settingsRules = [
                'email' => 'required|email',
                'age' => 'required|integer|min:18'
            ];
        };
        
        // Test valid data
        $validSettings = ['email' => 'test@example.com', 'age' => 25];
        $result = $modelWithRules->setSetting($validSettings);
        $this->assertInstanceOf(get_class($modelWithRules), $result);
        
        // Test invalid data should throw ValidationException
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $modelWithRules->setSetting(['email' => 'invalid-email', 'age' => 'not-a-number']);
    }

    public function test_cache_key_generation_with_timestamps(): void
    {
        // Add a setting
        $this->testModel->setSetting(['test_key' => 'test_value']);
        
        // Get the setting to populate cache
        $setting1 = $this->testModel->getSetting('test_key');
        $this->assertNotNull($setting1);
        
        // Update the model to change updated_at
        $this->testModel->touch();
        
        // Get setting again - should use new cache key due to timestamp change
        $setting2 = $this->testModel->getSetting('test_key');
        $this->assertNotNull($setting2);
        $this->assertEquals($setting1->value, $setting2->value);
    }

    public function test_legacy_methods_backward_compatibility(): void
    {
        // Test that legacy methods still work
        $rules = $this->testModel->getRules();
        $defaults = $this->testModel->getDefaultSettings();
        
        $this->assertIsArray($rules);
        $this->assertIsArray($defaults);
        
        // Should return same as hooked properties
        $this->assertEquals($this->testModel->rules, $rules);
        $this->assertEquals($this->testModel->defaultSettingsHooked, $defaults);
    }

    public function test_enhanced_cache_clearing(): void
    {
        // Enable cache for this test
        config(['core.cache.enabled' => true]);
        
        // Add settings
        $this->testModel->setSetting(['key1' => 'value1', 'key2' => 'value2']);
        
        // Get settings to populate cache
        $this->testModel->getSetting('key1');
        $this->testModel->allSetting();
        
        // Clear cache
        $this->testModel->clearSettingsCache();
        
        // Add new setting
        $this->testModel->setSetting(['key3' => 'value3']);
        
        // Should be able to get all settings including new one
        $allSettings = $this->testModel->allSetting();
        $this->assertCount(3, $allSettings);
        $this->assertArrayHasKey('key3', $allSettings);
    }

    public function test_update_setting_with_cache_invalidation(): void
    {
        // Enable cache
        config(['core.cache.enabled' => true]);
        
        // Add initial setting
        $this->testModel->setSetting(['test_key' => 'initial_value']);
        
        // Get setting to populate cache
        $setting = $this->testModel->getSetting('test_key');
        $this->assertEquals('initial_value', $setting->value);
        
        // Update setting
        $this->testModel->updateSetting('test_key', 'updated_value');
        
        // Get setting again - should have updated value
        $updatedSetting = $this->testModel->getSetting('test_key');
        $this->assertEquals('updated_value', $updatedSetting->value);
    }

    public function test_property_hooks_are_functional(): void
    {
        // Add some settings
        $this->testModel->setSetting(['theme' => 'dark', 'lang' => 'en']);
        $this->testModel->refresh();
        
        // Test all property hooks
        $this->assertIsArray($this->testModel->rules);
        $this->assertIsArray($this->testModel->defaultSettingsHooked);
        $this->assertIsInt($this->testModel->settingsCount);
        $this->assertIsBool($this->testModel->hasSettingsAttached);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $this->testModel->allSettingsCollection);
        $this->assertIsBool($this->testModel->isCacheEnabled);
        
        // Verify values
        $this->assertEquals(2, $this->testModel->settingsCount);
        $this->assertTrue($this->testModel->hasSettingsAttached);
        $this->assertCount(2, $this->testModel->allSettingsCollection);
    }
}
