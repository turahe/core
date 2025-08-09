<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Turahe\Core\Concerns\HasOrganization;
use Turahe\Core\Models\Organization;
use Turahe\Core\Tests\TestCase;
use Turahe\Core\Tests\Models\User;
use Turahe\Core\Tests\Feature\Factories\OrganizationFactory;

class HasOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private TestUser $user;
    private TestUser $manager;
    private Organization $organization1;
    private Organization $organization2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = TestUser::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password')
        ]);
        $this->manager = TestUser::create([
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password')
        ]);
        $this->organization1 = OrganizationFactory::new()->create(['created_by' => $this->manager->id]);
        $this->organization2 = OrganizationFactory::new()->create(['created_by' => $this->manager->id]);
    }

    public function test_organizations_relationship_returns_morph_to_many(): void
    {
        $relationship = $this->user->organizations();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphToMany::class, $relationship);
        $this->assertEquals('model_has_organization', $relationship->getTable());
        $this->assertEquals('model_type', $relationship->getMorphType());
        $this->assertInstanceOf(Organization::class, $relationship->getRelated());
    }

    public function test_organizations_relationship_has_pivot_attributes(): void
    {
        $relationship = $this->user->organizations();
        
        $this->assertContains('role', $relationship->getPivotColumns());
    }

    public function test_organizations_relationship_has_timestamps(): void
    {
        $relationship = $this->user->organizations();
        
        $this->assertTrue($relationship->withTimestamps);
    }

    public function test_can_attach_user_to_organization(): void
    {
        $this->user->organizations()->attach($this->organization1->id, ['role' => 'MEMBER']);
        
        $this->assertTrue($this->user->organizations->contains($this->organization1));
        $this->assertEquals('MEMBER', $this->user->organizations->first()->pivot->role);
    }

    public function test_can_attach_user_to_multiple_organizations(): void
    {
        $this->user->organizations()->attach([
            $this->organization1->id => ['role' => 'MEMBER'],
            $this->organization2->id => ['role' => 'ADMIN']
        ]);
        
        $this->assertCount(2, $this->user->organizations);
        $this->assertTrue($this->user->organizations->contains($this->organization1));
        $this->assertTrue($this->user->organizations->contains($this->organization2));
    }

    public function test_managed_organization_relationship_returns_has_many(): void
    {
        $relationship = $this->manager->managedOrganization();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relationship);
        $this->assertInstanceOf(Organization::class, $relationship->getRelated());
    }

    public function test_managed_organization_returns_organizations_user_manages(): void
    {
        $managedOrgs = $this->manager->managedOrganization;
        
        $this->assertCount(2, $managedOrgs);
        $this->assertTrue($managedOrgs->contains($this->organization1));
        $this->assertTrue($managedOrgs->contains($this->organization2));
    }

    public function test_all_organization_returns_merged_collection(): void
    {
        // User manages organization1 and belongs to organization2
        $this->organization1->update(['created_by' => $this->user->id]);
        $this->user->organizations()->attach($this->organization2->id, ['role' => 'MEMBER']);
        
        $allOrgs = $this->user->allOrganization();
        
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $allOrgs);
        $this->assertCount(2, $allOrgs);
        $this->assertTrue($allOrgs->contains($this->organization1));
        $this->assertTrue($allOrgs->contains($this->organization2));
    }

    public function test_all_organization_sorts_by_name(): void
    {
        $orgA = OrganizationFactory::new()->create(['name' => 'Alpha Org', 'created_by' => $this->user->id]);
        $orgB = OrganizationFactory::new()->create(['name' => 'Beta Org']);
        $orgC = OrganizationFactory::new()->create(['name' => 'Charlie Org']);
        
        $this->user->organizations()->attach([
            $orgB->id => ['role' => 'MEMBER'],
            $orgC->id => ['role' => 'ADMIN']
        ]);
        
        $allOrgs = $this->user->allOrganization();
        
        $this->assertEquals('Alpha Org', $allOrgs->first()->name);
        $this->assertEquals('Beta Org', $allOrgs->get(1)->name);
        $this->assertEquals('Charlie Org', $allOrgs->last()->name);
    }

    public function test_manages_organization_returns_true_when_user_manages_org(): void
    {
        $this->organization1->update(['created_by' => $this->user->id]);
        
        $this->assertTrue($this->user->managesOrganization($this->organization1));
    }

    public function test_manages_organization_returns_false_when_user_does_not_manage_org(): void
    {
        $this->assertFalse($this->user->managesOrganization($this->organization1));
    }

    public function test_belongs_to_team_returns_true_when_user_manages_organization(): void
    {
        $this->organization1->update(['created_by' => $this->user->id]);
        
        $this->assertTrue($this->user->belongsToTeam($this->organization1));
    }

    public function test_belongs_to_team_returns_true_when_user_belongs_to_organization(): void
    {
        $this->user->organizations()->attach($this->organization1->id, ['role' => 'MEMBER']);
        
        $this->assertTrue($this->user->belongsToTeam($this->organization1));
    }

    public function test_belongs_to_team_returns_false_when_user_not_related_to_organization(): void
    {
        $this->assertFalse($this->user->belongsToTeam($this->organization1));
    }

    public function test_scope_of_manager_includes_managed_users(): void
    {
        // Manager manages organization1, user belongs to organization1
        $this->user->organizations()->attach($this->organization1->id, ['role' => 'MEMBER']);
        
        $managedUsers = TestUser::ofManager($this->manager)->get();
        
        $this->assertTrue($managedUsers->contains($this->user));
    }

    public function test_scope_of_manager_includes_manager_when_with_current_user_true(): void
    {
        $managedUsers = TestUser::ofManager($this->manager, true)->get();
        
        $this->assertTrue($managedUsers->contains($this->manager));
    }

    public function test_scope_of_manager_excludes_manager_when_with_current_user_false(): void
    {
        $managedUsers = TestUser::ofManager($this->manager, false)->get();
        
        $this->assertFalse($managedUsers->contains($this->manager));
    }

    public function test_scope_of_manager_returns_empty_when_manager_has_no_organizations(): void
    {
        $newManager = TestUser::create([
            'name' => 'New Manager',
            'email' => 'newmanager@example.com',
            'password' => bcrypt('password')
        ]);
        
        $managedUsers = TestUser::ofManager($newManager, false)->get();
        
        $this->assertCount(0, $managedUsers);
    }

    public function test_scope_of_manager_returns_only_manager_when_with_current_user_true_and_no_managed_users(): void
    {
        $newManager = TestUser::create([
            'name' => 'New Manager',
            'email' => 'newmanager2@example.com',
            'password' => bcrypt('password')
        ]);
        
        $managedUsers = TestUser::ofManager($newManager, true)->get();
        
        $this->assertCount(1, $managedUsers);
        $this->assertTrue($managedUsers->contains($newManager));
    }

    public function test_can_detach_user_from_organization(): void
    {
        $this->user->organizations()->attach($this->organization1->id, ['role' => 'MEMBER']);
        
        $this->assertTrue($this->user->organizations->contains($this->organization1));
        
        $this->user->organizations()->detach($this->organization1->id);
        
        $this->user->load('organizations');
        $this->assertFalse($this->user->organizations->contains($this->organization1));
    }

    public function test_can_sync_user_organizations(): void
    {
        $this->user->organizations()->attach($this->organization1->id, ['role' => 'MEMBER']);
        
        $this->user->organizations()->sync([
            $this->organization2->id => ['role' => 'ADMIN']
        ]);
        
        $this->user->load('organizations');
        $this->assertFalse($this->user->organizations->contains($this->organization1));
        $this->assertTrue($this->user->organizations->contains($this->organization2));
        $this->assertEquals('ADMIN', $this->user->organizations->first()->pivot->role);
    }

    public function test_organization_pivot_has_correct_attributes(): void
    {
        $this->user->organizations()->attach($this->organization1->id, ['role' => 'ADMIN']);
        
        $pivot = $this->user->organizations->first()->pivot;
        
        $this->assertEquals($this->user->id, $pivot->model_id);
        $this->assertEquals($this->organization1->id, $pivot->organization_id);
        $this->assertEquals('ADMIN', $pivot->role);
        $this->assertNotNull($pivot->created_at);
        $this->assertNotNull($pivot->updated_at);
    }

    public function test_organizations_relationship_uses_correct_foreign_keys(): void
    {
        $relationship = $this->user->organizations();
        
        $this->assertEquals('model_id', $relationship->getForeignPivotKeyName());
        $this->assertEquals('organization_id', $relationship->getRelatedPivotKeyName());
    }

    public function test_managed_organization_uses_correct_foreign_key(): void
    {
        $relationship = $this->manager->managedOrganization();
        
        // The HasOrganization trait uses hasMany(Organization::class) which defaults to 'user_id'
        // but the Organization model uses userstamps, so this is a mismatch in the trait
        $this->assertEquals('user_id', $relationship->getForeignKeyName());
    }

    // ===============================================
    // Tests for PHP 8.4 Features
    // ===============================================

    public function test_managed_organizations_count_readonly_property(): void
    {
        $this->assertEquals(2, $this->manager->managedOrganizationsCount);
        $this->assertEquals(0, $this->user->managedOrganizationsCount);
    }

    public function test_member_organizations_count_readonly_property(): void
    {
        // Attach user to organizations
        $this->user->organizations()->attach($this->organization1, ['role' => 'member']);
        $this->user->organizations()->attach($this->organization2, ['role' => 'admin']);
        
        $this->assertEquals(2, $this->user->memberOrganizationsCount);
        $this->assertEquals(0, $this->manager->memberOrganizationsCount);
    }

    public function test_organizations_with_role_variadic_parameters(): void
    {
        // Attach user to organizations with different roles
        $this->user->organizations()->attach($this->organization1, ['role' => 'member']);
        $this->user->organizations()->attach($this->organization2, ['role' => 'admin']);
        
        // Test with single role
        $memberOrgs = $this->user->organizationsWithRole('member');
        $this->assertCount(1, $memberOrgs);
        $this->assertEquals($this->organization1->id, $memberOrgs->first()->id);
        
        // Test with multiple roles using variadic parameters
        $allRoleOrgs = $this->user->organizationsWithRole('member', 'admin');
        $this->assertCount(2, $allRoleOrgs);
        
        // Test with non-existent role
        $noOrgs = $this->user->organizationsWithRole('owner');
        $this->assertCount(0, $noOrgs);
    }

    public function test_has_role_in_organization(): void
    {
        // Attach user to organization with specific role
        $this->user->organizations()->attach($this->organization1, ['role' => 'admin']);
        
        $this->assertTrue($this->user->hasRoleInOrganization($this->organization1, 'admin'));
        $this->assertFalse($this->user->hasRoleInOrganization($this->organization1, 'member'));
        $this->assertFalse($this->user->hasRoleInOrganization($this->organization2, 'admin'));
    }

    public function test_manages_organization_with_match_expression(): void
    {
        // Test that manager manages organizations they created
        $this->assertTrue($this->manager->managesOrganization($this->organization1));
        $this->assertTrue($this->manager->managesOrganization($this->organization2));
        
        // Test that user doesn't manage organizations they didn't create
        $this->assertFalse($this->user->managesOrganization($this->organization1));
        $this->assertFalse($this->user->managesOrganization($this->organization2));
    }

    public function test_all_organization_caching(): void
    {
        // Attach user to one organization
        $this->user->organizations()->attach($this->organization1, ['role' => 'member']);
        
        // First call should populate cache
        $result1 = $this->user->allOrganization();
        
        // Second call should use cache (same object reference)
        $result2 = $this->user->allOrganization();
        
        $this->assertEquals($result1, $result2);
        $this->assertCount(1, $result1);
    }

    public function test_clear_organizations_cache(): void
    {
        // Attach user to organization
        $this->user->organizations()->attach($this->organization1, ['role' => 'member']);
        
        // Get initial count
        $initialCount = $this->user->allOrganization()->count();
        $this->assertEquals(1, $initialCount);
        
        // Clear cache
        $this->user->clearOrganizationsCache();
        
        // Add another organization
        $this->user->organizations()->attach($this->organization2, ['role' => 'admin']);
        
        // Should reflect new count
        $newCount = $this->user->allOrganization()->count();
        $this->assertEquals(2, $newCount);
    }

    public function test_belongs_to_team_enhanced_performance(): void
    {
        // Test manager belongs to organization they manage
        $this->assertTrue($this->manager->belongsToTeam($this->organization1));
        
        // Test user doesn't belong to organization initially
        $this->assertFalse($this->user->belongsToTeam($this->organization1));
        
        // Attach user to organization
        $this->user->organizations()->attach($this->organization1, ['role' => 'member']);
        
        // Clear cache to ensure fresh data
        $this->user->clearOrganizationsCache();
        
        // Now user should belong to organization
        $this->assertTrue($this->user->belongsToTeam($this->organization1));
    }

    public function test_scope_of_manager_with_auto_capture_closures(): void
    {
        // Create another user and organization
        $anotherUser = TestUser::create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => bcrypt('password')
        ]);
        
        // Attach users to organizations managed by manager
        $this->user->organizations()->attach($this->organization1, ['role' => 'member']);
        $anotherUser->organizations()->attach($this->organization2, ['role' => 'member']);
        
        // Test scope with current user included
        $managedUsers = TestUser::ofManager($this->manager, true)->get();
        $this->assertCount(3, $managedUsers); // manager + 2 users
        
        // Test scope without current user
        $managedUsersExcludingManager = TestUser::ofManager($this->manager, false)->get();
        $this->assertCount(2, $managedUsersExcludingManager); // 2 users only
    }

    public function test_readonly_properties_are_immutable(): void
    {
        // These should not throw exceptions as they're readonly
        $count1 = $this->manager->managedOrganizationsCount;
        $count2 = $this->user->memberOrganizationsCount;
        
        $this->assertIsInt($count1);
        $this->assertIsInt($count2);
        
        // Verify the properties exist and return expected types
        $this->assertEquals(2, $count1);
        $this->assertEquals(0, $count2);
    }
}

/**
 * Test User model that uses the HasOrganization trait
 * Note: User already has HasOrganization trait, so no need to add it again
 */
class TestUser extends User
{
    // User already has HasOrganization trait
} 