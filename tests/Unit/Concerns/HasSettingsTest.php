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
}
