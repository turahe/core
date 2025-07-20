<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Turahe\Core\Enums\OrganizationType;
use Turahe\Core\Models\Organization;
use Turahe\Core\Tests\Models\User;

class OrganizationTestSeeder extends Seeder
{
    /**
     * Run the database seeds for testing.
     */
    public function run(): void
    {
        // Create a test user
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

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

        $this->command->info('Test organizations seeded successfully!');
        $this->command->info('Created ' . Organization::count() . ' test organizations.');
    }
} 