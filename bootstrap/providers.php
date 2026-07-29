<?php

return [
    App\Providers\AppServiceProvider::class,
    // Registers Horizon (and any future dev-only package) in local only, and
    // only when the package is actually installed. See the class docblock.
    App\Providers\DevToolsServiceProvider::class,
    App\Modules\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Taxonomy\Providers\TaxonomyServiceProvider::class,
    App\Modules\Businesses\Providers\BusinessesServiceProvider::class,
    App\Modules\Products\Providers\ProductsServiceProvider::class,
    App\Modules\Messaging\Providers\MessagingServiceProvider::class,
    App\Modules\Saved\Providers\SavedServiceProvider::class,
    App\Modules\Notifications\Providers\NotificationsServiceProvider::class,
    App\Modules\Cms\Providers\CmsServiceProvider::class,
    App\Modules\Events\Providers\EventsServiceProvider::class,
    App\Modules\Support\Providers\SupportServiceProvider::class,
    App\Modules\Analytics\Providers\AnalyticsServiceProvider::class,
    App\Modules\ApiProduct\Providers\ApiProductServiceProvider::class,
    App\Modules\Admin\Providers\AdminServiceProvider::class,
];
