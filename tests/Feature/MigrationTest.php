<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Turahe\Core\Tests\TestCase;

class MigrationTest extends TestCase
{
    /**
     * Test settings table migration with different userstamps configurations
     */
    public function test_settings_table_migration_with_bigincrements_config(): void
    {
        // Set userstamps configuration to bigincrements
        $this->setUserstampsConfig('bigincrements');

        // Run the migration
        $this->artisan('migrate');

        // Assert the table exists
        $this->assertTrue(Schema::hasTable(config('core.tables.settings')));

        // Get table columns
        $columns = Schema::getColumnListing(config('core.tables.settings'));

        // Assert userstamp columns exist with correct types
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);

        // Check column types (this would require more complex schema inspection)
        // For now, just verify the columns exist
    }

    public function test_settings_table_migration_with_ulid_config(): void
    {
        // Set userstamps configuration to ulid
        $this->setUserstampsConfig('ulid');

        // Run the migration
        $this->artisan('migrate');

        // Assert the table exists
        $this->assertTrue(Schema::hasTable(config('core.tables.settings')));

        // Get table columns
        $columns = Schema::getColumnListing(config('core.tables.settings'));

        // Assert userstamp columns exist
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    public function test_settings_table_migration_with_uuid_config(): void
    {
        // Set userstamps configuration to uuid
        $this->setUserstampsConfig('uuid');

        // Run the migration
        $this->artisan('migrate');

        // Assert the table exists
        $this->assertTrue(Schema::hasTable(config('core.tables.settings')));

        // Get table columns
        $columns = Schema::getColumnListing(config('core.tables.settings'));

        // Assert userstamp columns exist
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    /**
     * Test organizations table migration with different userstamps configurations
     */
    public function test_organizations_table_migration_with_bigincrements_config(): void
    {
        $this->setUserstampsConfig('bigincrements');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.organizations')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_organization')));

        $columns = Schema::getColumnListing(config('core.tables.organizations'));
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    public function test_organizations_table_migration_with_ulid_config(): void
    {
        $this->setUserstampsConfig('ulid');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.organizations')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_organization')));

        $columns = Schema::getColumnListing(config('core.tables.organizations'));
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    public function test_organizations_table_migration_with_uuid_config(): void
    {
        $this->setUserstampsConfig('uuid');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.organizations')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_organization')));

        $columns = Schema::getColumnListing(config('core.tables.organizations'));
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    /**
     * Test taxonomies table migration with different userstamps configurations
     */
    public function test_taxonomies_table_migration_with_bigincrements_config(): void
    {
        $this->setUserstampsConfig('bigincrements');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.taxonomies')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_taxonomies')));

        $columns = Schema::getColumnListing(config('core.tables.taxonomies'));
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    public function test_taxonomies_table_migration_with_ulid_config(): void
    {
        $this->setUserstampsConfig('ulid');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.taxonomies')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_taxonomies')));

        $columns = Schema::getColumnListing(config('core.tables.taxonomies'));
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    public function test_taxonomies_table_migration_with_uuid_config(): void
    {
        $this->setUserstampsConfig('uuid');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.taxonomies')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_taxonomies')));

        $columns = Schema::getColumnListing(config('core.tables.taxonomies'));
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    /**
     * Test tags table migration with different userstamps configurations
     */
    public function test_tags_table_migration_with_bigincrements_config(): void
    {
        $this->setUserstampsConfig('bigincrements');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.tags')));
        $this->assertTrue(Schema::hasTable(config('core.tables.taggables')));

        $columns = Schema::getColumnListing(config('core.tables.tags'));
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    public function test_tags_table_migration_with_ulid_config(): void
    {
        $this->setUserstampsConfig('ulid');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.tags')));
        $this->assertTrue(Schema::hasTable(config('core.tables.taggables')));

        $columns = Schema::getColumnListing(config('core.tables.tags'));
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    public function test_tags_table_migration_with_uuid_config(): void
    {
        $this->setUserstampsConfig('uuid');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.tags')));
        $this->assertTrue(Schema::hasTable(config('core.tables.taggables')));

        $columns = Schema::getColumnListing(config('core.tables.tags'));
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }

    /**
     * Test OAuth accounts table migration with different userstamps configurations
     */
    public function test_oauth_accounts_table_migration_with_bigincrements_config(): void
    {
        $this->setUserstampsConfig('bigincrements');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.oauth_accounts')));

        $columns = Schema::getColumnListing(config('core.tables.oauth_accounts'));
        // OAuth accounts table doesn't have userstamps, but should have user_id foreign key
        $this->assertContains('user_id', $columns);
    }

    public function test_oauth_accounts_table_migration_with_ulid_config(): void
    {
        $this->setUserstampsConfig('ulid');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.oauth_accounts')));

        $columns = Schema::getColumnListing(config('core.tables.oauth_accounts'));
        $this->assertContains('user_id', $columns);
    }

    public function test_oauth_accounts_table_migration_with_uuid_config(): void
    {
        $this->setUserstampsConfig('uuid');
        $this->artisan('migrate');

        $this->assertTrue(Schema::hasTable(config('core.tables.oauth_accounts')));

        $columns = Schema::getColumnListing(config('core.tables.oauth_accounts'));
        $this->assertContains('user_id', $columns);
    }

    /**
     * Test that all migrations work together with different userstamps configurations
     */
    public function test_all_migrations_with_bigincrements_config(): void
    {
        $this->setUserstampsConfig('bigincrements');
        $this->artisan('migrate');

        // Test all tables exist
        $this->assertTrue(Schema::hasTable(config('core.tables.settings')));
        $this->assertTrue(Schema::hasTable(config('core.tables.organizations')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_organization')));
        $this->assertTrue(Schema::hasTable(config('core.tables.taxonomies')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_taxonomies')));
        $this->assertTrue(Schema::hasTable(config('core.tables.tags')));
        $this->assertTrue(Schema::hasTable(config('core.tables.taggables')));
        $this->assertTrue(Schema::hasTable(config('core.tables.oauth_accounts')));
    }

    public function test_all_migrations_with_ulid_config(): void
    {
        $this->setUserstampsConfig('ulid');
        $this->artisan('migrate');

        // Test all tables exist
        $this->assertTrue(Schema::hasTable(config('core.tables.settings')));
        $this->assertTrue(Schema::hasTable(config('core.tables.organizations')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_organization')));
        $this->assertTrue(Schema::hasTable(config('core.tables.taxonomies')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_taxonomies')));
        $this->assertTrue(Schema::hasTable(config('core.tables.tags')));
        $this->assertTrue(Schema::hasTable(config('core.tables.taggables')));
        $this->assertTrue(Schema::hasTable(config('core.tables.oauth_accounts')));
    }

    public function test_all_migrations_with_uuid_config(): void
    {
        $this->setUserstampsConfig('uuid');
        $this->artisan('migrate');

        // Test all tables exist
        $this->assertTrue(Schema::hasTable(config('core.tables.settings')));
        $this->assertTrue(Schema::hasTable(config('core.tables.organizations')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_organization')));
        $this->assertTrue(Schema::hasTable(config('core.tables.taxonomies')));
        $this->assertTrue(Schema::hasTable(config('core.tables.model_has_taxonomies')));
        $this->assertTrue(Schema::hasTable(config('core.tables.tags')));
        $this->assertTrue(Schema::hasTable(config('core.tables.taggables')));
        $this->assertTrue(Schema::hasTable(config('core.tables.oauth_accounts')));
    }

    /**
     * Test that migrations can be rolled back and re-run
     */
    public function test_migrations_can_be_rolled_back_and_rerun(): void
    {
        $this->setUserstampsConfig('ulid');

        // Run migrations
        $this->artisan('migrate');
        $this->assertTrue(Schema::hasTable(config('core.tables.settings')));

        // Rollback migrations
        $this->artisan('migrate:rollback');
        $this->assertFalse(Schema::hasTable(config('core.tables.settings')));

        // Run migrations again
        $this->artisan('migrate');
        $this->assertTrue(Schema::hasTable(config('core.tables.settings')));
    }

    /**
     * Test default configuration (should be ulid)
     */
    public function test_default_ulid_configuration(): void
    {
        // Use default ulid configuration
        $this->setUserstampsConfig('ulid');

        // Run the migration
        $this->artisan('migrate');

        // Assert the table exists
        $this->assertTrue(Schema::hasTable(config('core.tables.settings')));

        // Get table columns
        $columns = Schema::getColumnListing(config('core.tables.settings'));

        // Assert userstamp columns exist
        $this->assertContains('created_by', $columns);
        $this->assertContains('updated_by', $columns);
        $this->assertContains('deleted_by', $columns);
    }
}
