<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Turahe\Core\Models\OAuthAccount;
use Turahe\Core\Models\Organization;
use Turahe\Core\Models\Setting;
use Turahe\Core\Models\Tag;
use Turahe\Core\Models\Taxonomy;
use Turahe\Core\Tests\TestCase;

class ModelsConfigurablePrimaryKeyTest extends TestCase
{
    public function test_all_models_use_configurable_primary_key_trait(): void
    {
        $models = [
            new Setting,
            new Organization,
            new Taxonomy,
            new Tag,
            new OAuthAccount,
        ];

        foreach ($models as $model) {
            $this->assertTrue(
                method_exists($model, 'getConfiguredKeyType'),
                get_class($model).' should have getConfiguredKeyType method'
            );

            $this->assertTrue(
                method_exists($model, 'shouldUseUniqueIds'),
                get_class($model).' should have shouldUseUniqueIds method'
            );

            $this->assertTrue(
                method_exists($model, 'shouldUseIncrementing'),
                get_class($model).' should have shouldUseIncrementing method'
            );

            $this->assertTrue(
                method_exists($model, 'getPrimaryKeyType'),
                get_class($model).' should have getPrimaryKeyType method'
            );

            $this->assertTrue(
                method_exists($model, 'newUniqueId'),
                get_class($model).' should have newUniqueId method'
            );

            $this->assertTrue(
                method_exists($model, 'uniqueIds'),
                get_class($model).' should have uniqueIds method'
            );
        }
    }

    public function test_all_models_default_to_ulid_configuration(): void
    {
        $models = [
            new Setting,
            new Organization,
            new Taxonomy,
            new Tag,
            new OAuthAccount,
        ];

        foreach ($models as $model) {
            $this->assertEquals('ulid', $model->getPrimaryKeyType());
            $this->assertTrue($model->shouldUseUniqueIds());
            $this->assertFalse($model->shouldUseIncrementing());
            $this->assertEquals('string', $model->getConfiguredKeyType());
            $this->assertEquals(['id'], $model->uniqueIds());
        }
    }

    public function test_all_models_respond_to_configuration_changes(): void
    {
        $models = [
            new Setting,
            new Organization,
            new Taxonomy,
            new Tag,
            new OAuthAccount,
        ];

        // Test bigincrements configuration
        config(['userstamps.users_table_column_type' => 'bigincrements']);

        foreach ($models as $model) {
            $this->assertEquals('bigincrements', $model->getPrimaryKeyType());
            $this->assertFalse($model->shouldUseUniqueIds());
            $this->assertTrue($model->shouldUseIncrementing());
            $this->assertEquals('int', $model->getConfiguredKeyType());
            $this->assertEquals([], $model->uniqueIds());
        }

        // Test uuid configuration
        config(['userstamps.users_table_column_type' => 'uuid']);

        foreach ($models as $model) {
            $this->assertEquals('uuid', $model->getPrimaryKeyType());
            $this->assertTrue($model->shouldUseUniqueIds());
            $this->assertFalse($model->shouldUseIncrementing());
            $this->assertEquals('string', $model->getConfiguredKeyType());
            $this->assertEquals(['id'], $model->uniqueIds());
        }

        // Reset to default
        config(['userstamps.users_table_column_type' => 'ulid']);
    }

    public function test_all_models_can_generate_unique_ids(): void
    {
        $models = [
            new Setting,
            new Organization,
            new Taxonomy,
            new Tag,
            new OAuthAccount,
        ];

        // Test ULID generation
        config(['userstamps.users_table_column_type' => 'ulid']);

        foreach ($models as $model) {
            $ulid = $model->newUniqueId();
            $this->assertNotEmpty($ulid);
            $this->assertEquals(26, strlen($ulid)); // ULID is 26 characters
        }

        // Test UUID generation
        config(['userstamps.users_table_column_type' => 'uuid']);

        foreach ($models as $model) {
            $uuid = $model->newUniqueId();
            $this->assertNotEmpty($uuid);
            $this->assertEquals(36, strlen($uuid)); // UUID is 36 characters
        }

        // Reset to default
        config(['userstamps.users_table_column_type' => 'ulid']);
    }

    public function test_all_models_have_correct_key_type_and_incrementing(): void
    {
        $models = [
            new Setting,
            new Organization,
            new Taxonomy,
            new Tag,
            new OAuthAccount,
        ];

        // Test ULID configuration
        config(['userstamps.users_table_column_type' => 'ulid']);

        foreach ($models as $model) {
            $this->assertEquals('string', $model->getKeyType());
            $this->assertFalse($model->getIncrementing());
        }

        // Test bigincrements configuration
        config(['userstamps.users_table_column_type' => 'bigincrements']);

        foreach ($models as $model) {
            $this->assertEquals('int', $model->getKeyType());
            $this->assertTrue($model->getIncrementing());
        }

        // Test UUID configuration
        config(['userstamps.users_table_column_type' => 'uuid']);

        foreach ($models as $model) {
            $this->assertEquals('string', $model->getKeyType());
            $this->assertFalse($model->getIncrementing());
        }

        // Reset to default
        config(['userstamps.users_table_column_type' => 'ulid']);
    }
}
