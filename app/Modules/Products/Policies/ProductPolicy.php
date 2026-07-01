<?php

namespace App\Modules\Products\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Products\Models\Product;

class ProductPolicy
{
    public function manage(User $user, Product $product): bool
    {
        return $product->business?->user_id === $user->id
            || $user->hasRole(['admin', 'super_admin', 'moderator']);
    }
}
