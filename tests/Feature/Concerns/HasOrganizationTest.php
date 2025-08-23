<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Feature\Concerns;

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

    private User $user;
    private User $manager;
    private Organization $organization1;
    private Organization $organization2;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password')
        ]);
        $this->manager = User::create([
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password')
        ]);
        
        // Set the manager as the authenticated user so HasUserStamps works
        $this->actingAs($this->manager);
        
        $this->organization1 = OrganizationFactory::new()->create();
        $this->organization2 = OrganizationFactory::new()->create();
        
        // The HasUserStamps trait will automatically set created_by when creating organizations
        // No need to manually update the created_by field
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
        // Create organizations through the manager user so created_by is set correctly
        $org1 = OrganizationFactory::new()->create();
        $org2 = OrganizationFactory::new()->create();
        
        // The HasUserStamps trait should automatically set created_by to the current user
        // But in tests, we need to ensure the manager is the one creating them
        $org1->update(['created_by' => $this->manager->id]);
        $org2->update(['created_by' => $this->manager->id]);
        
        $managedOrgs = $this->manager->managedOrganization;
        
        // Filter to only include the organizations we created in this test
        $testManagedOrgs = $managedOrgs->whereIn('id', [$org1->id, $org2->id]);
        
        $this->assertCount(2, $testManagedOrgs);
        $this->assertTrue($testManagedOrgs->contains($org1));
        $this->assertTrue($testManagedOrgs->contains($org2));
    }

    public function test_all_organization_returns_merged_collection(): void
    {
        // Switch to user context to create an organization they manage
        $this->actingAs($this->user);
        $userOrg = OrganizationFactory::new()->create();
        
        // User belongs to organization2 (created by manager)
        $this->user->organizations()->attach($this->organization2->id, ['role' => 'MEMBER']);
        
        $allOrgs = $this->user->allOrganization();
        
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $allOrgs);
        $this->assertCount(2, $allOrgs);
        $this->assertTrue($allOrgs->contains($userOrg));
        $this->assertTrue($allOrgs->contains($this->organization2));
    }

    public function test_all_organization_sorts_by_name(): void
    {
        // Switch to user context to create an organization they manage
        $this->actingAs($this->user);
        $orgA = OrganizationFactory::new()->create(['name' => 'Alpha Org']);
        
        // Switch back to manager context for setup
        $this->actingAs($this->manager);
        $orgB = OrganizationFactory::new()->create(['name' => 'Beta Org']);
        $orgC = OrganizationFactory::new()->create(['name' => 'Charlie Org']);
        
        $this->user->organizations()->attach([
            $orgB->id => ['role' => 'MEMBER'],
            $orgC->id => ['role' => 'ADMIN']
        ]);
        
        // Debug: Check what organizations the user manages and belongs to
        $managedOrgs = $this->user->managedOrganization;
        $belongedOrgs = $this->user->organizations;
        
        $this->assertCount(1, $managedOrgs);
        $this->assertCount(2, $belongedOrgs);
        
        $allOrgs = $this->user->allOrganization();
        
        // Debug: Check the actual order
        $names = $allOrgs->pluck('name')->toArray();
        $this->assertEquals(['Alpha Org', 'Beta Org', 'Charlie Org'], $names);
    }

    public function test_manages_organization_returns_true_when_user_manages_org(): void
    {
        // Switch to user context to create an organization they manage
        $this->actingAs($this->user);
        $userOrg = OrganizationFactory::new()->create();
        
        $this->assertTrue($this->user->managesOrganization($userOrg));
    }

    public function test_manages_organization_returns_false_when_user_does_not_manage_org(): void
    {
        $this->assertFalse($this->user->managesOrganization($this->organization1));
    }

    public function test_belongs_to_team_returns_true_when_user_manages_organization(): void
    {
        // Switch to user context to create an organization they manage
        $this->actingAs($this->user);
        $userOrg = OrganizationFactory::new()->create();
        
        $this->assertTrue($this->user->belongsToTeam($userOrg));
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
        $this->organization1->update(['created_by' => $this->manager->id]);
        $this->user->organizations()->attach($this->organization1->id, ['role' => 'MEMBER']);
        
        $managedUsers = User::ofManager($this->manager)->get();
        
        $this->assertTrue($managedUsers->contains($this->user));
    }

    public function test_scope_of_manager_includes_manager_when_with_current_user_true(): void
    {
        $managedUsers = User::ofManager($this->manager, true)->get();
        
        $this->assertTrue($managedUsers->contains($this->manager));
    }

    public function test_scope_of_manager_excludes_manager_when_with_current_user_false(): void
    {
        $managedUsers = User::ofManager($this->manager, false)->get();
        
        $this->assertFalse($managedUsers->contains($this->manager));
    }

    public function test_scope_of_manager_returns_empty_when_manager_has_no_organizations(): void
    {
        $newManager = User::create([
            'name' => 'New Manager',
            'email' => 'newmanager@example.com',
            'password' => bcrypt('password')
        ]);
        
        $managedUsers = User::ofManager($newManager, false)->get();
        
        $this->assertCount(0, $managedUsers);
    }

    public function test_scope_of_manager_returns_only_manager_when_with_current_user_true_and_no_managed_users(): void
    {
        $newManager = User::create([
            'name' => 'New Manager',
            'email' => 'newmanager2@example.com',
            'password' => bcrypt('password')
        ]);
        
        $managedUsers = User::ofManager($newManager, true)->get();
        
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
        
        // The HasOrganization trait uses hasMany(Organization::class, 'created_by') 
        // because the Organization model uses userstamps with 'created_by' field
        $this->assertEquals('created_by', $relationship->getForeignKeyName());
    }
}

