<?php

namespace App\Modules\ApiProduct\Providers;

use Illuminate\Support\ServiceProvider;

class ApiProductServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
