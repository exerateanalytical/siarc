<?php

namespace App\Shared\Enums;

enum VerificationStatus: string
{
    case Unverified = 'unverified';
    case Basic      = 'basic';
    case Verified   = 'verified';
    case Certified  = 'certified';
}
