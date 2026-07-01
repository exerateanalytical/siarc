<?php

namespace App\Modules\Saved\Providers;

use Illuminate\Support\ServiceProvider;

class SavedServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
