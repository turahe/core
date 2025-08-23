<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Concerns;

use Illuminate\Database\Eloquent\Model;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;
use Turahe\Core\Tests\TestCase;

class HasConfigurablePrimaryKeyTest extends TestCase
{
    public function test_trait_provides_configuration_methods(): void
    {
        $model = new TestModel;

        $this->assertTrue(method_exists($model, 'getConfiguredKeyType'));
        $this->assertTrue(method_exists($model, 'shouldUseIncrementing'));
        $this->assertTrue(method_exists($model, 'shouldUseUniqueIds'));
        $this->assertTrue(method_exists($model, 'getPrimaryKeyType'));
        $this->assertTrue(method_exists($model, 'usesBigIncrements'));
        $this->assertTrue(method_exists($model, 'usesUlids'));
        $this->assertTrue(method_exists($model, 'usesUuids'));
        $this->assertTrue(method_exists($model, 'newUniqueId'));
        $this->assertTrue(method_exists($model, 'uniqueIds'));
        $this->assertTrue(method_exists($model, 'getKeyType'));
        $this->assertTrue(method_exists($model, 'getIncrementing'));
    }

    public function test_default_configuration_is_ulid(): void
    {
        $model = new TestModel;

        $this->assertEquals('ulid', $model->getPrimaryKeyType());
        $this->assertTrue($model->shouldUseUniqueIds());
        $this->assertFalse($model->shouldUseIncrementing());
        $this->assertEquals('string', $model->getConfiguredKeyType());
        $this->assertFalse($model->usesBigIncrements());
        $this->assertTrue($model->usesUlids());
        $this->assertFalse($model->usesUuids());
        $this->assertEquals('string', $model->getKeyType());
        $this->assertFalse($model->getIncrementing());
    }

    public function test_bigincrements_configuration(): void
    {
        config(['userstamps.users_table_column_type' => 'bigincrements']);

        $model = new TestModel;

        $this->assertEquals('bigincrements', $model->getPrimaryKeyType());
        $this->assertFalse($model->shouldUseUniqueIds());
        $this->assertTrue($model->shouldUseIncrementing());
        $this->assertEquals('int', $model->getConfiguredKeyType());
        $this->assertTrue($model->usesBigIncrements());
        $this->assertFalse($model->usesUlids());
        $this->assertFalse($model->usesUuids());
        $this->assertEquals('int', $model->getKeyType());
        $this->assertTrue($model->getIncrementing());
    }

    public function test_ulid_configuration(): void
    {
        config(['userstamps.users_table_column_type' => 'ulid']);

        $model = new TestModel;

        $this->assertEquals('ulid', $model->getPrimaryKeyType());
        $this->assertTrue($model->shouldUseUniqueIds());
        $this->assertFalse($model->shouldUseIncrementing());
        $this->assertEquals('string', $model->getConfiguredKeyType());
        $this->assertFalse($model->usesBigIncrements());
        $this->assertTrue($model->usesUlids());
        $this->assertFalse($model->usesUuids());
        $this->assertEquals('string', $model->getKeyType());
        $this->assertFalse($model->getIncrementing());
    }

    public function test_uuid_configuration(): void
    {
        config(['userstamps.users_table_column_type' => 'uuid']);

        $model = new TestModel;

        $this->assertEquals('uuid', $model->getPrimaryKeyType());
        $this->assertTrue($model->shouldUseUniqueIds());
        $this->assertFalse($model->shouldUseIncrementing());
        $this->assertEquals('string', $model->getConfiguredKeyType());
        $this->assertFalse($model->usesBigIncrements());
        $this->assertFalse($model->usesUlids());
        $this->assertTrue($model->usesUuids());
        $this->assertEquals('string', $model->getKeyType());
        $this->assertFalse($model->getIncrementing());
    }

    public function test_invalid_configuration_returns_configured_value(): void
    {
        config(['userstamps.users_table_column_type' => 'invalid']);

        $model = new TestModel;

        $this->assertEquals('invalid', $model->getPrimaryKeyType());
        // Invalid configuration should not use unique IDs since it's not 'ulid' or 'uuid'
        $this->assertFalse($model->shouldUseUniqueIds());
        $this->assertFalse($model->shouldUseIncrementing());
        $this->assertEquals('string', $model->getConfiguredKeyType());
        $this->assertFalse($model->usesBigIncrements());
        // Invalid configuration is not specifically ulid or uuid
        $this->assertFalse($model->usesUlids());
        $this->assertFalse($model->usesUuids());
        $this->assertEquals('string', $model->getKeyType());
        $this->assertFalse($model->getIncrementing());
    }

    public function test_primary_key_name_is_always_id(): void
    {
        $model = new TestModel;

        $this->assertEquals('id', $model->getKeyName());

        // Test with different configurations
        config(['userstamps.users_table_column_type' => 'bigincrements']);
        $this->assertEquals('id', $model->getKeyName());

        config(['userstamps.users_table_column_type' => 'uuid']);
        $this->assertEquals('id', $model->getKeyName());
    }

    public function test_configuration_changes_are_reflected_dynamically(): void
    {
        $model = new TestModel;

        // Start with default (ulid)
        $this->assertTrue($model->shouldUseUniqueIds());
        $this->assertFalse($model->shouldUseIncrementing());

        // Change to bigincrements
        config(['userstamps.users_table_column_type' => 'bigincrements']);
        $this->assertFalse($model->shouldUseUniqueIds());
        $this->assertTrue($model->shouldUseIncrementing());

        // Change to uuid
        config(['userstamps.users_table_column_type' => 'uuid']);
        $this->assertTrue($model->shouldUseUniqueIds());
        $this->assertFalse($model->shouldUseIncrementing());
    }

    public function test_new_unique_id_generation(): void
    {
        $model = new TestModel;

        // Test ULID generation
        config(['userstamps.users_table_column_type' => 'ulid']);
        $ulid = $model->newUniqueId();
        $this->assertNotEmpty($ulid);
        $this->assertEquals(26, strlen($ulid)); // ULID is 26 characters

        // Test UUID generation
        config(['userstamps.users_table_column_type' => 'uuid']);
        $uuid = $model->newUniqueId();
        $this->assertNotEmpty($uuid);
        $this->assertEquals(36, strlen($uuid)); // UUID is 36 characters

        // Test bigincrements (should still generate ULID as fallback)
        config(['userstamps.users_table_column_type' => 'bigincrements']);
        $fallback = $model->newUniqueId();
        $this->assertNotEmpty($fallback);
        $this->assertEquals(26, strlen($fallback)); // Should fallback to ULID
    }

    public function test_unique_ids_array(): void
    {
        $model = new TestModel;

        // Test ULID configuration
        config(['userstamps.users_table_column_type' => 'ulid']);
        $this->assertEquals(['id'], $model->uniqueIds());

        // Test UUID configuration
        config(['userstamps.users_table_column_type' => 'uuid']);
        $this->assertEquals(['id'], $model->uniqueIds());

        // Test bigincrements configuration
        config(['userstamps.users_table_column_type' => 'bigincrements']);
        $this->assertEquals([], $model->uniqueIds());
    }
}

/**
 * Test model that uses the HasConfigurablePrimaryKey trait
 */
class TestModel extends Model
{
    use HasConfigurablePrimaryKey;
}
