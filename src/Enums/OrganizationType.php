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
 * The values represent common organizational types including:
 * - Business entities (Company, Supplier, Holding, Subsidiary)
 * - Physical locations (Branch, Outlet, Store, Office)
 * - Organizational units (Department, Division, Designation)
 * - Institutional types (Foundation, Institution, Community)
 * - Business relationships (Partner, Franchisee)
 * 
 * @package Turahe\Core\Enums
 */
enum OrganizationType: string
{
    /** Company or corporation entity */
    case Company = 'COMPANY';

    /** External supplier or vendor organization */
    case Supplier = 'SUPPLIER';

    /** Parent company that owns other companies */
    case CompanyHolding = 'COMPANY_HOLDING';

    /** Company owned by a holding company */
    case CompanySubsidiary = 'COMPANY_SUBSIDIARY';

    /** Regional or local branch of a larger organization */
    case Branch = 'BRANCH';

    /** Retail or service outlet location */
    case Outlet = 'OUTLET';

    /** Retail store location */
    case Store = 'STORE';

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

    /** Educational or governmental institution */
    case Institution = 'INSTITUTION';

    /** Community or social group organization */
    case Community = 'COMMUNITY';

    /** Generic organization type */
    case Organization = 'ORGANIZATION';

    /** Non-profit foundation organization */
    case Foundation = 'FOUNDATION';

    /** Branch office location */
    case BranchOffice = 'BRANCH_OFFICE';

    /** Branch outlet location */
    case BranchOutlet = 'BRANCH_OUTLET';

    /** Branch store location */
    case BranchStore = 'BRANCH_STORE';

    /** Regional organizational unit */
    case Regional = 'REGIONAL';

    /** Franchise business partner */
    case Franchisee = 'FRANCHISEE';

    /** Business partner organization */
    case Partner = 'PARTNER';

}
