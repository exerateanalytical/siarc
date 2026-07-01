<?php

namespace App\Modules\Businesses\Providers;

use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Policies\BusinessPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BusinessesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');

        Gate::policy(Business::class, BusinessPolicy::class);
    }
}
