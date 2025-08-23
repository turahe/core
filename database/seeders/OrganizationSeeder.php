<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Turahe\Core\Enums\OrganizationType;
use Turahe\Core\Models\Organization;
use Turahe\Core\Tests\Models\User;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default user for seeding if none exists
        $defaultUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
            ]
        );

        // Create main company
        $mainCompany = Organization::create([
            'name' => 'Turahe Corporation',
            'code' => 'TUR',
            'type' => OrganizationType::Company,
            'created_by' => $defaultUser->id,
        ]);

        // Create holding company
        $holdingCompany = Organization::create([
            'name' => 'Turahe Holdings',
            'code' => 'THL',
            'type' => OrganizationType::CompanyHolding,
            'created_by' => $defaultUser->id,
        ]);

        // Create subsidiaries
        $subsidiary1 = Organization::create([
            'name' => 'Turahe Tech Solutions',
            'code' => 'TTS',
            'type' => OrganizationType::CompanySubsidiary,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        $subsidiary2 = Organization::create([
            'name' => 'Turahe Digital Services',
            'code' => 'TDS',
            'type' => OrganizationType::CompanySubsidiary,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create branches
        $branch1 = Organization::create([
            'name' => 'Jakarta Branch',
            'code' => 'JKT',
            'type' => OrganizationType::Branch,
            'parent_id' => $subsidiary1->id,
            'created_by' => $defaultUser->id,
        ]);

        $branch2 = Organization::create([
            'name' => 'Surabaya Branch',
            'code' => 'SBY',
            'type' => OrganizationType::Branch,
            'parent_id' => $subsidiary1->id,
            'created_by' => $defaultUser->id,
        ]);

        $branch3 = Organization::create([
            'name' => 'Bandung Branch',
            'code' => 'BDG',
            'type' => OrganizationType::Branch,
            'parent_id' => $subsidiary2->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create departments
        $hrDepartment = Organization::create([
            'name' => 'Human Resources',
            'code' => 'HR',
            'type' => OrganizationType::Department,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        $itDepartment = Organization::create([
            'name' => 'Information Technology',
            'code' => 'IT',
            'type' => OrganizationType::Department,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        $financeDepartment = Organization::create([
            'name' => 'Finance',
            'code' => 'FIN',
            'type' => OrganizationType::Department,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        $marketingDepartment = Organization::create([
            'name' => 'Marketing',
            'code' => 'MKT',
            'type' => OrganizationType::Department,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create sub-departments
        $recruitmentSubDept = Organization::create([
            'name' => 'Recruitment',
            'code' => 'REC',
            'type' => OrganizationType::SubDepartment,
            'parent_id' => $hrDepartment->id,
            'created_by' => $defaultUser->id,
        ]);

        $payrollSubDept = Organization::create([
            'name' => 'Payroll',
            'code' => 'PAY',
            'type' => OrganizationType::SubDepartment,
            'parent_id' => $hrDepartment->id,
            'created_by' => $defaultUser->id,
        ]);

        $developmentSubDept = Organization::create([
            'name' => 'Software Development',
            'code' => 'DEV',
            'type' => OrganizationType::SubDepartment,
            'parent_id' => $itDepartment->id,
            'created_by' => $defaultUser->id,
        ]);

        $infrastructureSubDept = Organization::create([
            'name' => 'Infrastructure',
            'code' => 'INF',
            'type' => OrganizationType::SubDepartment,
            'parent_id' => $itDepartment->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create divisions
        $salesDivision = Organization::create([
            'name' => 'Sales Division',
            'code' => 'SALES',
            'type' => OrganizationType::Division,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        $operationsDivision = Organization::create([
            'name' => 'Operations Division',
            'code' => 'OPS',
            'type' => OrganizationType::Division,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create sub-divisions
        $domesticSalesSubDiv = Organization::create([
            'name' => 'Domestic Sales',
            'code' => 'DOM',
            'type' => OrganizationType::SubDivision,
            'parent_id' => $salesDivision->id,
            'created_by' => $defaultUser->id,
        ]);

        $internationalSalesSubDiv = Organization::create([
            'name' => 'International Sales',
            'code' => 'INT',
            'type' => OrganizationType::SubDivision,
            'parent_id' => $salesDivision->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create outlets/stores
        $outlet1 = Organization::create([
            'name' => 'Jakarta Central Outlet',
            'code' => 'JCO',
            'type' => OrganizationType::Outlet,
            'parent_id' => $branch1->id,
            'created_by' => $defaultUser->id,
        ]);

        $outlet2 = Organization::create([
            'name' => 'Surabaya Mall Store',
            'code' => 'SMS',
            'type' => OrganizationType::Store,
            'parent_id' => $branch2->id,
            'created_by' => $defaultUser->id,
        ]);

        $outlet3 = Organization::create([
            'name' => 'Bandung Plaza Outlet',
            'code' => 'BPO',
            'type' => OrganizationType::Outlet,
            'parent_id' => $branch3->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create suppliers
        $supplier1 = Organization::create([
            'name' => 'Tech Supplies Co.',
            'code' => 'TSC',
            'type' => OrganizationType::Supplier,
            'created_by' => $defaultUser->id,
        ]);

        $supplier2 = Organization::create([
            'name' => 'Office Equipment Ltd.',
            'code' => 'OEL',
            'type' => OrganizationType::Supplier,
            'created_by' => $defaultUser->id,
        ]);

        // Create partners
        $partner1 = Organization::create([
            'name' => 'Digital Solutions Partner',
            'code' => 'DSP',
            'type' => OrganizationType::Partner,
            'created_by' => $defaultUser->id,
        ]);

        $partner2 = Organization::create([
            'name' => 'Cloud Services Alliance',
            'code' => 'CSA',
            'type' => OrganizationType::Partner,
            'created_by' => $defaultUser->id,
        ]);

        // Create franchisees
        $franchisee1 = Organization::create([
            'name' => 'Medan Franchise',
            'code' => 'MDF',
            'type' => OrganizationType::Franchisee,
            'created_by' => $defaultUser->id,
        ]);

        $franchisee2 = Organization::create([
            'name' => 'Makassar Franchise',
            'code' => 'MKF',
            'type' => OrganizationType::Franchisee,
            'created_by' => $defaultUser->id,
        ]);

        // Create regional offices
        $regionalWest = Organization::create([
            'name' => 'Western Regional Office',
            'code' => 'WRO',
            'type' => OrganizationType::Regional,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        $regionalEast = Organization::create([
            'name' => 'Eastern Regional Office',
            'code' => 'ERO',
            'type' => OrganizationType::Regional,
            'parent_id' => $mainCompany->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create branch offices
        $branchOffice1 = Organization::create([
            'name' => 'Semarang Branch Office',
            'code' => 'SBO',
            'type' => OrganizationType::BranchOffice,
            'parent_id' => $regionalWest->id,
            'created_by' => $defaultUser->id,
        ]);

        $branchOffice2 = Organization::create([
            'name' => 'Malang Branch Office',
            'code' => 'MBO',
            'type' => OrganizationType::BranchOffice,
            'parent_id' => $regionalEast->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create institutions
        $institution1 = Organization::create([
            'name' => 'Turahe Training Institute',
            'code' => 'TTI',
            'type' => OrganizationType::Institution,
            'created_by' => $defaultUser->id,
        ]);

        // Create foundations
        $foundation1 = Organization::create([
            'name' => 'Turahe Foundation',
            'code' => 'TF',
            'type' => OrganizationType::Foundation,
            'created_by' => $defaultUser->id,
        ]);

        // Create communities
        $community1 = Organization::create([
            'name' => 'Turahe Developer Community',
            'code' => 'TDC',
            'type' => OrganizationType::Community,
            'created_by' => $defaultUser->id,
        ]);

        // Create designations
        $designation1 = Organization::create([
            'name' => 'Senior Developer',
            'code' => 'SDEV',
            'type' => OrganizationType::Designation,
            'parent_id' => $developmentSubDept->id,
            'created_by' => $defaultUser->id,
        ]);

        $designation2 = Organization::create([
            'name' => 'Project Manager',
            'code' => 'PM',
            'type' => OrganizationType::Designation,
            'parent_id' => $operationsDivision->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create branch outlets and stores
        $branchOutlet1 = Organization::create([
            'name' => 'Semarang Mall Outlet',
            'code' => 'SMO',
            'type' => OrganizationType::BranchOutlet,
            'parent_id' => $branchOffice1->id,
            'created_by' => $defaultUser->id,
        ]);

        $branchStore1 = Organization::create([
            'name' => 'Malang Plaza Store',
            'code' => 'MPS',
            'type' => OrganizationType::BranchStore,
            'parent_id' => $branchOffice2->id,
            'created_by' => $defaultUser->id,
        ]);

        // Create general organizations
        $generalOrg1 = Organization::create([
            'name' => 'Turahe Alumni Association',
            'code' => 'TAA',
            'type' => OrganizationType::Organization,
            'created_by' => $defaultUser->id,
        ]);

        $this->command->info('Organizations seeded successfully!');
        $this->command->info('Created '.Organization::count().' organizations with hierarchical structure.');
    }
}
