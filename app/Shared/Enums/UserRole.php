<?php

namespace App\Shared\Enums;

enum UserRole: string
{
    case SuperAdmin   = 'super_admin';
    case GovtReviewer = 'govt_reviewer';
    case CmfReviewer  = 'cmf_reviewer';
    case CompanyOwner = 'company_owner';
    case CompanyMember = 'company_member';
    case Investor     = 'investor';
    case Public       = 'public';
}
