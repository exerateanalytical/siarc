<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint_responds(): void
    {
        // Framework health route registered in bootstrap/app.php
        $this->get('/up')->assertStatus(200);
    }
}
