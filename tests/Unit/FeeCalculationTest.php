<?php

namespace Tests\Unit;

use App\Modules\Payments\Services\FeeCalculationService;
use Tests\TestCase;

class FeeCalculationTest extends TestCase
{
    private FeeCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FeeCalculationService();
    }

    public function test_calculates_platform_fee(): void
    {
        $result = $this->service->calculate(100000);

        // 2.5% of 100,000 = 2,500
        $this->assertEquals(2500, $result['platform_fee_xaf']);
    }

    public function test_calculates_vat_on_fee(): void
    {
        $result = $this->service->calculate(100000);

        // 19.25% of 2,500 = 481.25 → rounded = 481
        $this->assertEquals(481, $result['vat_xaf']);
    }

    public function test_calculates_net_amount(): void
    {
        $result = $this->service->calculate(100000);

        // net = 100,000 - 2,500 - 481 = 97,019
        $this->assertEquals(97019, $result['net_amount_xaf']);
    }

    public function test_calculates_total_fee(): void
    {
        $result = $this->service->calculate(100000);

        $this->assertEquals(2981, $result['total_fee_xaf']); // 2500 + 481
    }
}
