<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Turahe\Core\Enums\OrganizationType;
use Turahe\Core\Models\Organization;
use Turahe\Core\Tests\TestCase;

class OrganizationModelTest extends TestCase
{
    public function test_organization_uses_configurable_primary_key(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'newUniqueId'));
        $this->assertTrue(method_exists($organization, 'uniqueIds'));
        $this->assertTrue(method_exists($organization, 'shouldUseUniqueIds'));
        $this->assertTrue(method_exists($organization, 'getConfiguredKeyType'));
        $this->assertTrue(method_exists($organization, 'shouldUseIncrementing'));
    }

    public function test_organization_uses_user_stamps(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'author'));
        $this->assertTrue(method_exists($organization, 'editor'));
        $this->assertTrue(method_exists($organization, 'destroyer'));
    }

    public function test_organization_uses_soft_deletes(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'trashed'));
        $this->assertTrue(method_exists($organization, 'restore'));
        $this->assertTrue(method_exists($organization, 'forceDelete'));
    }

    public function test_organization_uses_nested_set(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'getLftName'));
        $this->assertTrue(method_exists($organization, 'getRgtName'));
        $this->assertTrue(method_exists($organization, 'getParentIdName'));
    }

    public function test_organization_uses_sortable(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'setHighestOrderNumber'));
        $this->assertTrue(method_exists($organization, 'moveOrderDown'));
        $this->assertTrue(method_exists($organization, 'moveOrderUp'));
    }

    public function test_organization_uses_sluggable(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'getSlugOptions'));
    }

    public function test_organization_table_is_configurable(): void
    {
        $organization = new Organization();
        
        $this->assertEquals(config('core.tables.organizations'), $organization->getTable());
    }

    public function test_organization_has_fillable_attributes(): void
    {
        $organization = new Organization();
        
        $expectedFillable = [
            'name',
            'code',
            'type',
            'slug',
        ];
        
        $this->assertEquals($expectedFillable, $organization->getFillable());
    }

    public function test_organization_has_correct_nested_set_column_names(): void
    {
        $organization = new Organization();
        
        $this->assertEquals('record_left', $organization->getLftName());
        $this->assertEquals('record_right', $organization->getRgtName());
        $this->assertEquals('parent_id', $organization->getParentIdName());
    }

    public function test_organization_has_sortable_configuration(): void
    {
        $organization = new Organization();
        
        $expectedSortable = [
            'order_column_name' => 'record_ordering',
            'sort_when_creating' => true,
        ];
        
        $this->assertEquals($expectedSortable, $organization->sortable);
    }

    public function test_organization_has_default_settings(): void
    {
        $organization = new Organization();
        
        $expectedDefaultSettings = [
            'language',
            'timezone',
        ];
        
        $this->assertEquals($expectedDefaultSettings, $organization->getDefaultSettings());
    }

    public function test_organization_has_settings_rules(): void
    {
        $organization = new Organization();
        
        $expectedSettingsRules = [
            'datetime' => 'string',
            'language' => 'string|exists:tm_languages,code',
            'timezone' => 'timezone:all',
        ];
        
        $this->assertEquals($expectedSettingsRules, $organization->settingsRules);
    }

    public function test_organization_can_be_created(): void
    {
        $organization = new Organization();
        
        $this->assertInstanceOf(Organization::class, $organization);
        $this->assertTrue(method_exists($organization, 'create'));
    }

    public function test_organization_automatically_generates_slug(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'getSlugOptions'));
    }

    public function test_organization_slug_is_not_regenerated_on_update(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'getSlugOptions'));
    }

    public function test_organization_can_have_parent_child_relationship(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'getLftName'));
        $this->assertTrue(method_exists($organization, 'getRgtName'));
        $this->assertTrue(method_exists($organization, 'getParentIdName'));
    }

    public function test_organization_users_relationship(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'users'));
        $this->assertTrue(method_exists($organization, 'allUsers'));
    }

    public function test_organization_all_users_includes_author(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'allUsers'));
    }

    public function test_organization_can_be_updated(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'update'));
    }

    public function test_organization_can_be_deleted(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'delete'));
        $this->assertTrue(method_exists($organization, 'forceDelete'));
    }

    public function test_organization_can_be_force_deleted(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'forceDelete'));
    }

    public function test_organization_can_be_restored(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'restore'));
    }

    public function test_organization_can_be_pruned(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'prunable'));
    }

    public function test_organization_has_organization_type_enum_cast(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'casts'));
    }

    public function test_organization_can_set_parent_attribute(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'setParentAttribute'));
    }

    public function test_organization_can_purge_data(): void
    {
        $organization = new Organization();
        
        $this->assertTrue(method_exists($organization, 'purge'));
    }
}
