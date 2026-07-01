<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Illuminate\Foundation\Testing\TestCase as FoundationTestCase;

abstract class TestCase extends FoundationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (in_array(RefreshDatabase::class, class_uses_recursive(static::class), true)) {
            $this->ensurePersonalAccessClient();
            $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        }
    }

    /**
     * Create a Passport personal access client for token issuance in tests.
     */
    protected function ensurePersonalAccessClient(): void
    {
        $exists = Client::query()->where('revoked', false)->get()
            ->contains(fn (Client $client): bool => $client->hasGrantType('personal_access'));

        if ($exists) {
            return;
        }

        Client::create([
            'id'            => (string) Str::uuid(),
            'name'          => 'Test Personal Access Client',
            'secret'        => Str::random(40),
            'provider'      => 'users',
            'redirect_uris' => [],
            'grant_types'   => ['personal_access'],
            'revoked'       => false,
        ]);
    }
}
