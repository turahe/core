<?php

declare(strict_types=1);

namespace Turahe\Core\Enums;

enum OrganizationType: string
{
    case Company = 'COMPANY';

    case Supplier = 'SUPPLIER';

    case CompanyHolding = 'COMPANY_HOLDING';

    case CompanySubsidiary = 'COMPANY_SUBSIDIARY';

    case Branch = 'BRANCH';

    case Outlet = 'OUTLET';

    case Store = 'STORE';

    case Department = 'DEPARTMENT';

    case SubDepartment = 'SUB_DEPARTMENT';

    case Division = 'DIVISION';

    case SubDivision = 'SUB_DIVISION';

    case Designation = 'DESIGNATION';

    case Institution = 'INSTITUTION';

    case Community = 'COMMUNITY';

    case Organization = 'ORGANIZATION';

    case Foundation = 'FOUNDATION';

    case BranchOffice = 'BRANCH_OFFICE';

    case BranchOutlet = 'BRANCH_OUTLET';

    case BranchStore = 'BRANCH_STORE';

    case Regional = 'REGIONAL';

    case Franchisee = 'FRANCHISEE';

    case Partner = 'PARTNER';

}
