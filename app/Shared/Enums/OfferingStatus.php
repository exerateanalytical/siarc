<?php

namespace App\Shared\Enums;

enum OfferingStatus: string
{
    case Draft       = 'draft';
    case PendingCmf  = 'pending_cmf';
    case CmfApproved = 'cmf_approved';
    case Open        = 'open';
    case Paused      = 'paused';
    case Closed      = 'closed';
    case Cancelled   = 'cancelled';
    case Completed   = 'completed';
}
