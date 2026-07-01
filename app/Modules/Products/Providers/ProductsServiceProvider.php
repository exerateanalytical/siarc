<?php

namespace App\Modules\Products\Providers;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ProductsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');

        Gate::policy(Product::class, ProductPolicy::class);
    }
}
