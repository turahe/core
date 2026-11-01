<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use PHPUnit\Framework\TestCase;
use Turahe\Core\Enums\OrganizationType;

class OrganizationTypeTest extends TestCase
{
    public function test_enum_has_expected_cases(): void
    {
        $this->assertEquals('COMPANY', OrganizationType::Company->value);
        $this->assertEquals('SUPPLIER', OrganizationType::Supplier->value);
        $this->assertEquals('BRANCH', OrganizationType::Branch->value);
    }

    public function test_get_business_entities_returns_array(): void
    {
        $businessEntities = OrganizationType::getBusinessEntities();
        $this->assertIsArray($businessEntities);
        $this->assertCount(6, $businessEntities);
        $this->assertContains(OrganizationType::Company, $businessEntities);
        $this->assertContains(OrganizationType::Supplier, $businessEntities);
        $this->assertContains(OrganizationType::CompanyHolding, $businessEntities);
        $this->assertContains(OrganizationType::CompanySubsidiary, $businessEntities);
        $this->assertContains(OrganizationType::Partner, $businessEntities);
        $this->assertContains(OrganizationType::Franchisee, $businessEntities);
    }

    public function test_get_physical_locations_returns_array(): void
    {
        $physicalLocations = OrganizationType::getPhysicalLocations();
        $this->assertIsArray($physicalLocations);
        $this->assertCount(7, $physicalLocations);
        $this->assertContains(OrganizationType::Branch, $physicalLocations);
        $this->assertContains(OrganizationType::BranchOffice, $physicalLocations);
        $this->assertContains(OrganizationType::Outlet, $physicalLocations);
        $this->assertContains(OrganizationType::Store, $physicalLocations);
        $this->assertContains(OrganizationType::Regional, $physicalLocations);
    }

    public function test_is_business_entity_methods(): void
    {
        $this->assertTrue(OrganizationType::Company->isBusinessEntity());
        $this->assertTrue(OrganizationType::Supplier->isBusinessEntity());
        $this->assertFalse(OrganizationType::Branch->isBusinessEntity());
        $this->assertFalse(OrganizationType::Department->isBusinessEntity());
    }

    public function test_is_physical_location_methods(): void
    {
        $this->assertTrue(OrganizationType::Branch->isPhysicalLocation());
        $this->assertTrue(OrganizationType::Store->isPhysicalLocation());
        $this->assertFalse(OrganizationType::Company->isPhysicalLocation());
        $this->assertFalse(OrganizationType::Department->isPhysicalLocation());
    }

    public function test_get_display_name_methods(): void
    {
        $this->assertEquals('Company', OrganizationType::Company->getDisplayName());
        $this->assertEquals('Supplier', OrganizationType::Supplier->getDisplayName());
        $this->assertEquals('Branch', OrganizationType::Branch->getDisplayName());
        $this->assertEquals('Department', OrganizationType::Department->getDisplayName());
    }

    public function test_get_description_methods(): void
    {
        $this->assertEquals('A company or corporation entity', OrganizationType::Company->getDescription());
        $this->assertEquals('An external supplier or vendor organization', OrganizationType::Supplier->getDescription());
        $this->assertEquals('A regional or local branch of a larger organization', OrganizationType::Branch->getDescription());
        $this->assertEquals('An organizational department within a company', OrganizationType::Department->getDescription());
    }

    public function test_is_organizational_unit(): void
    {
        $this->assertTrue(OrganizationType::Department->isOrganizationalUnit());
        $this->assertTrue(OrganizationType::Division->isOrganizationalUnit());
        $this->assertFalse(OrganizationType::Company->isOrganizationalUnit());
    }

    public function test_is_institutional(): void
    {
        $this->assertTrue(OrganizationType::Foundation->isInstitutional());
        $this->assertTrue(OrganizationType::Community->isInstitutional());
        $this->assertFalse(OrganizationType::Store->isInstitutional());
    }

    public function test_is_branch_type(): void
    {
        $this->assertTrue(OrganizationType::BranchOffice->isBranchType());
        $this->assertTrue(OrganizationType::BranchStore->isBranchType());
        $this->assertFalse(OrganizationType::Regional->isBranchType());
    }

    public function test_is_sub_type(): void
    {
        $this->assertTrue(OrganizationType::SubDepartment->isSubType());
        $this->assertTrue(OrganizationType::SubDivision->isSubType());
        $this->assertFalse(OrganizationType::Department->isSubType());
    }

    public function test_get_organizational_units_returns_the_unit_cases(): void
    {
        $units = OrganizationType::getOrganizationalUnits();

        $this->assertCount(5, $units);
        $this->assertContains(OrganizationType::Department, $units);
        $this->assertContains(OrganizationType::SubDepartment, $units);
        $this->assertContains(OrganizationType::Division, $units);
        $this->assertContains(OrganizationType::SubDivision, $units);
        $this->assertContains(OrganizationType::Designation, $units);
    }

    public function test_get_institutional_types_returns_the_institutional_cases(): void
    {
        $types = OrganizationType::getInstitutionalTypes();

        $this->assertCount(4, $types);
        $this->assertContains(OrganizationType::Institution, $types);
        $this->assertContains(OrganizationType::Community, $types);
        $this->assertContains(OrganizationType::Foundation, $types);
        $this->assertContains(OrganizationType::Organization, $types);
    }

    public function test_static_caching_works(): void
    {
        $businessEntities1 = OrganizationType::getBusinessEntities();
        $businessEntities2 = OrganizationType::getBusinessEntities();

        $physicalLocations1 = OrganizationType::getPhysicalLocations();
        $physicalLocations2 = OrganizationType::getPhysicalLocations();

        // Verify that the same array references are returned (caching works)
        $this->assertSame($businessEntities1, $businessEntities2);
        $this->assertSame($physicalLocations1, $physicalLocations2);
    }
}
