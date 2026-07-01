<?php

namespace App\Modules\Businesses\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;

class BusinessPolicy
{
    public function view(User $user, Business $business): bool
    {
        return $business->status === 'published' || $this->manage($user, $business);
    }

    public function update(User $user, Business $business): bool
    {
        return $this->manage($user, $business);
    }

    public function delete(User $user, Business $business): bool
    {
        return $this->manage($user, $business);
    }

    public function manage(User $user, Business $business): bool
    {
        return $business->user_id === $user->id
            || $user->hasRole(['admin', 'super_admin', 'moderator']);
    }
}
