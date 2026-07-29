<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

/**
 * NOTE: this class is only ever loaded by App\Providers\DevToolsServiceProvider,
 * which registers it in the local environment and only when laravel/horizon is
 * installed. It extends a Horizon class, so it cannot be autoloaded at all once
 * `composer install --no-dev` has run — never name it in bootstrap/providers.php.
 */
class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * Only consulted in non-local environments, which the registration gate
     * already rules out — so this is the second lock on a door that should not
     * exist. It reads the platform's own admin roles rather than a hand-kept
     * list of email addresses, so there is no separately-maintained truth to
     * drift: whoever administers the site administers the queue, nobody else.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            return $user !== null
                && method_exists($user, 'hasAnyRole')
                && $user->hasAnyRole(['super_admin', 'admin']);
        });
    }
}
