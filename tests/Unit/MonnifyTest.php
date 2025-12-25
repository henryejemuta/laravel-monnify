<?php

namespace HenryEjemuta\LaravelMonnify\Tests\Unit;

use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\LaravelMonnifyServiceProvider;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Http;

class MonnifyTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelMonnifyServiceProvider::class];
    }

    /** @test */
    public function it_can_compute_request_validation_hash()
    {
        $hash = Monnify::computeRequestValidationHash('test_data');
        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);
    }
}
