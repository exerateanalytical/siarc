<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as FoundationTestCase;

abstract class TestCase extends FoundationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (in_array(RefreshDatabase::class, class_uses_recursive(static::class), true)) {
            // SiacRolesSeeder creates the roles under the sanctum guard the
            // User model actually uses (RolesAndPermissionsSeeder is legacy, guard 'api')
            $this->seed(\Database\Seeders\Siac\SiacRolesSeeder::class);
        }
    }
}
