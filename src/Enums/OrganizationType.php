<?php

declare(strict_types=1);

namespace Turahe\Core\Enums;

/**
 * Organization Type Enum
 *
 * Defines the various types of organizations that can be represented
 * in the system. This enum provides a standardized way to categorize
 * different organizational structures and relationships.
 *
 * Features:
 * - Categorized organization types for better organization
 * - Helper methods for type checking and grouping
 * - Optimized for performance with static caching
 * - Comprehensive documentation for each case
 */
enum OrganizationType: string
{
    // ========================================
    // BUSINESS ENTITIES
    // ========================================

    /** Company or corporation entity */
    case Company = 'COMPANY';

    /** External supplier or vendor organization */
    case Supplier = 'SUPPLIER';

    /** Parent company that owns other companies */
    case CompanyHolding = 'COMPANY_HOLDING';

    /** Company owned by a holding company */
    case CompanySubsidiary = 'COMPANY_SUBSIDIARY';

    /** Business partner organization */
    case Partner = 'PARTNER';

    /** Franchise business partner */
    case Franchisee = 'FRANCHISEE';

    // ========================================
    // PHYSICAL LOCATIONS
    // ========================================

    /** Regional or local branch of a larger organization */
    case Branch = 'BRANCH';

    /** Branch office location */
    case BranchOffice = 'BRANCH_OFFICE';

    /** Retail or service outlet location */
    case Outlet = 'OUTLET';

    /** Branch outlet location */
    case BranchOutlet = 'BRANCH_OUTLET';

    /** Retail store location */
    case Store = 'STORE';

    /** Branch store location */
    case BranchStore = 'BRANCH_STORE';

    /** Regional organizational unit */
    case Regional = 'REGIONAL';

    // ========================================
    // ORGANIZATIONAL UNITS
    // ========================================

    /** Organizational department within a company */
    case Department = 'DEPARTMENT';

    /** Sub-department within a larger department */
    case SubDepartment = 'SUB_DEPARTMENT';

    /** Organizational division within a company */
    case Division = 'DIVISION';

    /** Sub-division within a larger division */
    case SubDivision = 'SUB_DIVISION';

    /** Job title or position designation */
    case Designation = 'DESIGNATION';

    // ========================================
    // INSTITUTIONAL TYPES
    // ========================================

    /** Educational or governmental institution */
    case Institution = 'INSTITUTION';

    /** Community or social group organization */
    case Community = 'COMMUNITY';

    /** Non-profit foundation organization */
    case Foundation = 'FOUNDATION';

    /** Generic organization type */
    case Organization = 'ORGANIZATION';

    /**
     * Get all business entity types
     *
     * @return array<self>
     */
    public static function getBusinessEntities(): array
    {
        static $types = null;
        if ($types === null) {
            $types = [
                self::Company,
                self::Supplier,
                self::CompanyHolding,
                self::CompanySubsidiary,
                self::Partner,
                self::Franchisee,
            ];
        }

        return $types;
    }

    /**
     * Get all physical location types
     *
     * @return array<self>
     */
    public static function getPhysicalLocations(): array
    {
        static $types = null;
        if ($types === null) {
            $types = [
                self::Branch,
                self::BranchOffice,
                self::Outlet,
                self::BranchOutlet,
                self::Store,
                self::BranchStore,
                self::Regional,
            ];
        }

        return $types;
    }

    /**
     * Get all organizational unit types
     *
     * @return array<self>
     */
    public static function getOrganizationalUnits(): array
    {
        static $types = null;
        if ($types === null) {
            $types = [
                self::Department,
                self::SubDepartment,
                self::Division,
                self::SubDivision,
                self::Designation,
            ];
        }

        return $types;
    }

    /**
     * Get all institutional types
     *
     * @return array<self>
     */
    public static function getInstitutionalTypes(): array
    {
        static $types = null;
        if ($types === null) {
            $types = [
                self::Institution,
                self::Community,
                self::Foundation,
                self::Organization,
            ];
        }

        return $types;
    }

    /**
     * Check if this type is a business entity
     */
    public function isBusinessEntity(): bool
    {
        return in_array($this, self::getBusinessEntities(), true);
    }

    /**
     * Check if this type is a physical location
     */
    public function isPhysicalLocation(): bool
    {
        return in_array($this, self::getPhysicalLocations(), true);
    }

    /**
     * Check if this type is an organizational unit
     */
    public function isOrganizationalUnit(): bool
    {
        return in_array($this, self::getOrganizationalUnits(), true);
    }

    /**
     * Check if this type is institutional
     */
    public function isInstitutional(): bool
    {
        return in_array($this, self::getInstitutionalTypes(), true);
    }

    /**
     * Check if this type is a branch-related type
     */
    public function isBranchType(): bool
    {
        return in_array($this, [
            self::Branch,
            self::BranchOffice,
            self::BranchOutlet,
            self::BranchStore,
        ], true);
    }

    /**
     * Check if this type is a sub-type (sub-department, sub-division)
     */
    public function isSubType(): bool
    {
        return in_array($this, [
            self::SubDepartment,
            self::SubDivision,
        ], true);
    }

    /**
     * Get the display name for this organization type
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::Company => 'Company',
            self::Supplier => 'Supplier',
            self::CompanyHolding => 'Company Holding',
            self::CompanySubsidiary => 'Company Subsidiary',
            self::Branch => 'Branch',
            self::Outlet => 'Outlet',
            self::Store => 'Store',
            self::Department => 'Department',
            self::SubDepartment => 'Sub Department',
            self::Division => 'Division',
            self::SubDivision => 'Sub Division',
            self::Designation => 'Designation',
            self::Institution => 'Institution',
            self::Community => 'Community',
            self::Organization => 'Organization',
            self::Foundation => 'Foundation',
            self::BranchOffice => 'Branch Office',
            self::BranchOutlet => 'Branch Outlet',
            self::BranchStore => 'Branch Store',
            self::Regional => 'Regional',
            self::Franchisee => 'Franchisee',
            self::Partner => 'Partner',
        };
    }

    /**
     * Get the description for this organization type
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::Company => 'A company or corporation entity',
            self::Supplier => 'An external supplier or vendor organization',
            self::CompanyHolding => 'A parent company that owns other companies',
            self::CompanySubsidiary => 'A company owned by a holding company',
            self::Branch => 'A regional or local branch of a larger organization',
            self::Outlet => 'A retail or service outlet location',
            self::Store => 'A retail store location',
            self::Department => 'An organizational department within a company',
            self::SubDepartment => 'A sub-department within a larger department',
            self::Division => 'An organizational division within a company',
            self::SubDivision => 'A sub-division within a larger division',
            self::Designation => 'A job title or position designation',
            self::Institution => 'An educational or governmental institution',
            self::Community => 'A community or social group organization',
            self::Organization => 'A generic organization type',
            self::Foundation => 'A non-profit foundation organization',
            self::BranchOffice => 'A branch office location',
            self::BranchOutlet => 'A branch outlet location',
            self::BranchStore => 'A branch store location',
            self::Regional => 'A regional organizational unit',
            self::Franchisee => 'A franchise business partner',
            self::Partner => 'A business partner organization',
        };
    }
}
