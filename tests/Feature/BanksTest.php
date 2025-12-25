<?php

namespace HenryEjemuta\LaravelMonnify\Tests\Feature;

use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class BanksTest extends TestCase
{
    public function test_get_banks()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'accessToken' => 'access_token',
                    'expiresIn' => 3600
                ]
            ]), 200),
            '*/api/v1/banks' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    [
                        'name' => 'Test Bank',
                        'code' => '001',
                        'ussdTemplate' => '*001*000*000#'
                    ]
                ]
            ]), 200)
        ]);

        $banks = Monnify::Banks()->getBanks();
        $this->assertIsArray($banks);
        $this->assertEquals('Test Bank', $banks[0]->name);
    }

    public function test_get_banks_with_ussd_short_code()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'accessToken' => 'access_token',
                    'expiresIn' => 3600
                ]
            ]), 200),
            '*/api/v1/sdk/transactions/banks' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    [
                        'name' => 'Test Bank USSD',
                        'code' => '002',
                        'baseUssdCode' => '*002#'
                    ]
                ]
            ]), 200)
        ]);

        $banks = Monnify::Banks()->getBanksWithUSSDShortCode();
        $this->assertIsArray($banks);
        $this->assertEquals('Test Bank USSD', $banks[0]->name);
    }
}
