<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Turahe\Core\Enums\OrganizationType;
use Turahe\Core\Models\Organization;
use Turahe\Core\Tests\TestCase;
use Turahe\Core\Tests\Models\User;

class OrganizationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_creation_with_hierarchical_structure(): void
    {
        // Create a test user
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create main company
        $mainCompany = Organization::create([
            'name' => 'Test Company',
            'code' => 'TEST',
            'type' => OrganizationType::Company,
            'created_by' => $testUser->id,
        ]);

        // Create a department
        $department = Organization::create([
            'name' => 'Test Department',
            'code' => 'TD',
            'type' => OrganizationType::Department,
            'parent_id' => $mainCompany->id,
            'created_by' => $testUser->id,
        ]);

        // Create a branch
        $branch = Organization::create([
            'name' => 'Test Branch',
            'code' => 'TB',
            'type' => OrganizationType::Branch,
            'parent_id' => $mainCompany->id,
            'created_by' => $testUser->id,
        ]);

        // Create a supplier
        $supplier = Organization::create([
            'name' => 'Test Supplier',
            'code' => 'TS',
            'type' => OrganizationType::Supplier,
            'created_by' => $testUser->id,
        ]);

        // Create a partner
        $partner = Organization::create([
            'name' => 'Test Partner',
            'code' => 'TP',
            'type' => OrganizationType::Partner,
            'created_by' => $testUser->id,
        ]);

        // Assert that organizations were created
        $this->assertDatabaseCount('organizations', 5);

        // Check specific organizations
        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Company',
            'code' => 'TEST',
        ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Department',
            'code' => 'TD',
        ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Branch',
            'code' => 'TB',
        ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Supplier',
            'code' => 'TS',
        ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Partner',
            'code' => 'TP',
        ]);

        // Check hierarchical relationships
        $this->assertEquals($mainCompany->id, $department->parent_id);
        $this->assertEquals($mainCompany->id, $branch->parent_id);
        $this->assertNull($supplier->parent_id);
        $this->assertNull($partner->parent_id);

        // Rebuild the tree to ensure nested set values are correct
        Organization::fixTree();

        // Debug: Check the actual values
        $department->refresh();
        $branch->refresh();
        
        $this->assertEquals($mainCompany->id, $department->parent_id, "Department parent_id should be {$mainCompany->id}, but got {$department->parent_id}");
        $this->assertEquals($mainCompany->id, $branch->parent_id, "Branch parent_id should be {$mainCompany->id}, but got {$branch->parent_id}");
    }

    public function test_comprehensive_organization_structure(): void
    {
        // Create a test user
        $testUser = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create main company
        $mainCompany = Organization::create([
            'name' => 'Turahe Corporation',
            'code' => 'TUR',
            'type' => OrganizationType::Company,
            'created_by' => $testUser->id,
        ]);

        // Create subsidiaries
        $subsidiary1 = Organization::create([
            'name' => 'Turahe Tech Solutions',
            'code' => 'TTS',
            'type' => OrganizationType::CompanySubsidiary,
            'parent_id' => $mainCompany->id,
            'created_by' => $testUser->id,
        ]);

        $subsidiary2 = Organization::create([
            'name' => 'Turahe Digital Services',
            'code' => 'TDS',
            'type' => OrganizationType::CompanySubsidiary,
            'parent_id' => $mainCompany->id,
            'created_by' => $testUser->id,
        ]);

        // Create branches
        $branch1 = Organization::create([
            'name' => 'Jakarta Branch',
            'code' => 'JKT',
            'type' => OrganizationType::Branch,
            'parent_id' => $subsidiary1->id,
            'created_by' => $testUser->id,
        ]);

        $branch2 = Organization::create([
            'name' => 'Surabaya Branch',
            'code' => 'SBY',
            'type' => OrganizationType::Branch,
            'parent_id' => $subsidiary1->id,
            'created_by' => $testUser->id,
        ]);

        // Create departments
        $hrDepartment = Organization::create([
            'name' => 'Human Resources',
            'code' => 'HR',
            'type' => OrganizationType::Department,
            'parent_id' => $mainCompany->id,
            'created_by' => $testUser->id,
        ]);

        $itDepartment = Organization::create([
            'name' => 'Information Technology',
            'code' => 'IT',
            'type' => OrganizationType::Department,
            'parent_id' => $mainCompany->id,
            'created_by' => $testUser->id,
        ]);

        // Create sub-departments
        $recruitmentSubDept = Organization::create([
            'name' => 'Recruitment',
            'code' => 'REC',
            'type' => OrganizationType::SubDepartment,
            'parent_id' => $hrDepartment->id,
            'created_by' => $testUser->id,
        ]);

        $developmentSubDept = Organization::create([
            'name' => 'Software Development',
            'code' => 'DEV',
            'type' => OrganizationType::SubDepartment,
            'parent_id' => $itDepartment->id,
            'created_by' => $testUser->id,
        ]);

        // Create outlets/stores
        $outlet1 = Organization::create([
            'name' => 'Jakarta Central Outlet',
            'code' => 'JCO',
            'type' => OrganizationType::Outlet,
            'parent_id' => $branch1->id,
            'created_by' => $testUser->id,
        ]);

        $outlet2 = Organization::create([
            'name' => 'Surabaya Mall Store',
            'code' => 'SMS',
            'type' => OrganizationType::Store,
            'parent_id' => $branch2->id,
            'created_by' => $testUser->id,
        ]);

        // Create external organizations
        $supplier1 = Organization::create([
            'name' => 'Tech Supplies Co.',
            'code' => 'TSC',
            'type' => OrganizationType::Supplier,
            'created_by' => $testUser->id,
        ]);

        $partner1 = Organization::create([
            'name' => 'Digital Solutions Partner',
            'code' => 'DSP',
            'type' => OrganizationType::Partner,
            'created_by' => $testUser->id,
        ]);

        $franchisee1 = Organization::create([
            'name' => 'Medan Franchise',
            'code' => 'MDF',
            'type' => OrganizationType::Franchisee,
            'created_by' => $testUser->id,
        ]);

        // Assert that organizations were created
        $this->assertGreaterThan(10, Organization::count());

        // Check hierarchical relationships
        $this->assertEquals($mainCompany->id, $subsidiary1->parent_id);
        $this->assertEquals($mainCompany->id, $subsidiary2->parent_id);
        $this->assertEquals($subsidiary1->id, $branch1->parent_id);
        $this->assertEquals($subsidiary1->id, $branch2->parent_id);
        $this->assertEquals($mainCompany->id, $hrDepartment->parent_id);
        $this->assertEquals($mainCompany->id, $itDepartment->parent_id);
        $this->assertEquals($hrDepartment->id, $recruitmentSubDept->parent_id);
        $this->assertEquals($itDepartment->id, $developmentSubDept->parent_id);
        $this->assertEquals($branch1->id, $outlet1->parent_id);
        $this->assertEquals($branch2->id, $outlet2->parent_id);

        // Check that external organizations have no parent
        $this->assertNull($supplier1->parent_id);
        $this->assertNull($partner1->parent_id);
        $this->assertNull($franchisee1->parent_id);
    }

    public function test_organization_types_coverage(): void
    {
        // Create a test user
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create organizations of different types
        $organizations = [
            ['name' => 'Test Company', 'code' => 'COMP', 'type' => OrganizationType::Company],
            ['name' => 'Test Holding', 'code' => 'HOLD', 'type' => OrganizationType::CompanyHolding],
            ['name' => 'Test Subsidiary', 'code' => 'SUB', 'type' => OrganizationType::CompanySubsidiary],
            ['name' => 'Test Branch', 'code' => 'BRANCH', 'type' => OrganizationType::Branch],
            ['name' => 'Test Department', 'code' => 'DEPT', 'type' => OrganizationType::Department],
            ['name' => 'Test Sub Department', 'code' => 'SUBDEPT', 'type' => OrganizationType::SubDepartment],
            ['name' => 'Test Division', 'code' => 'DIV', 'type' => OrganizationType::Division],
            ['name' => 'Test Sub Division', 'code' => 'SUBDIV', 'type' => OrganizationType::SubDivision],
            ['name' => 'Test Outlet', 'code' => 'OUT', 'type' => OrganizationType::Outlet],
            ['name' => 'Test Store', 'code' => 'STORE', 'type' => OrganizationType::Store],
            ['name' => 'Test Supplier', 'code' => 'SUPP', 'type' => OrganizationType::Supplier],
            ['name' => 'Test Partner', 'code' => 'PART', 'type' => OrganizationType::Partner],
            ['name' => 'Test Franchisee', 'code' => 'FRAN', 'type' => OrganizationType::Franchisee],
            ['name' => 'Test Regional', 'code' => 'REG', 'type' => OrganizationType::Regional],
            ['name' => 'Test Branch Office', 'code' => 'BO', 'type' => OrganizationType::BranchOffice],
            ['name' => 'Test Institution', 'code' => 'INST', 'type' => OrganizationType::Institution],
            ['name' => 'Test Foundation', 'code' => 'FOUND', 'type' => OrganizationType::Foundation],
            ['name' => 'Test Community', 'code' => 'COMM', 'type' => OrganizationType::Community],
            ['name' => 'Test Designation', 'code' => 'DESIG', 'type' => OrganizationType::Designation],
            ['name' => 'Test Branch Outlet', 'code' => 'BOUT', 'type' => OrganizationType::BranchOutlet],
            ['name' => 'Test Branch Store', 'code' => 'BSTORE', 'type' => OrganizationType::BranchStore],
            ['name' => 'Test Organization', 'code' => 'ORG', 'type' => OrganizationType::Organization],
        ];

        foreach ($organizations as $org) {
            Organization::create([
                'name' => $org['name'],
                'code' => $org['code'],
                'type' => $org['type'],
                'created_by' => $testUser->id,
            ]);
        }

        // Check that all organization types are represented
        $types = Organization::distinct()->pluck('type')->toArray();
        
        $expectedTypes = [
            OrganizationType::Company,
            OrganizationType::CompanyHolding,
            OrganizationType::CompanySubsidiary,
            OrganizationType::Branch,
            OrganizationType::Department,
            OrganizationType::SubDepartment,
            OrganizationType::Division,
            OrganizationType::SubDivision,
            OrganizationType::Outlet,
            OrganizationType::Store,
            OrganizationType::Supplier,
            OrganizationType::Partner,
            OrganizationType::Franchisee,
            OrganizationType::Regional,
            OrganizationType::BranchOffice,
            OrganizationType::Institution,
            OrganizationType::Foundation,
            OrganizationType::Community,
            OrganizationType::Designation,
            OrganizationType::BranchOutlet,
            OrganizationType::BranchStore,
            OrganizationType::Organization,
        ];

        foreach ($expectedTypes as $type) {
            $this->assertContains($type, $types, "Organization type {$type->value} should be created");
        }
    }

    public function test_organization_created_by_relationship(): void
    {
        // Create a test user
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Set the user as authenticated so HasUserStamps works
        $this->actingAs($testUser);

        // Create organizations with the authenticated user context
        $organization1 = Organization::create([
            'name' => 'Test Organization 1',
            'code' => 'TO1',
            'type' => OrganizationType::Company,
        ]);

        $organization2 = Organization::create([
            'name' => 'Test Organization 2',
            'code' => 'TO2',
            'type' => OrganizationType::Department,
            'parent_id' => $organization1->id,
        ]);

        // Verify created_by was set automatically by HasUserStamps
        $this->assertEquals($testUser->id, $organization1->created_by, "Organization 1 created_by should be {$testUser->id}, but got {$organization1->created_by}");
        $this->assertEquals($testUser->id, $organization2->created_by, "Organization 2 created_by should be {$testUser->id}, but got {$organization2->created_by}");

        // Check that the user can access their created organizations
        $userOrganizations = Organization::where('created_by', $testUser->id)->get();
        $this->assertCount(2, $userOrganizations);
        $this->assertTrue($userOrganizations->contains($organization1));
        $this->assertTrue($userOrganizations->contains($organization2));
    }
} 