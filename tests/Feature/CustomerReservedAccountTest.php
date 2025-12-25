<?php

namespace HenryEjemuta\LaravelMonnify\Tests\Feature;

use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class CustomerReservedAccountTest extends TestCase
{
    public function test_reserve_account()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => ['accessToken' => 'access_token', 'expiresIn' => 3600]
            ]), 200),
            '*/api/v1/bank-transfer/reserved-accounts' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'contractCode' => '1234567890',
                    'accountReference' => 'REF-001',
                    'accountName' => 'Test Account'
                ]
            ]), 200)
        ]);

        $response = Monnify::ReservedAccounts()->reserveAccount('REF-001', 'Test Account', 'test@example.com');
        $this->assertNotNull($response);
        $this->assertEquals('REF-001', $response->accountReference);
    }

    public function test_get_account_details()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => ['accessToken' => 'access_token', 'expiresIn' => 3600]
            ]), 200),
            '*/api/v1/bank-transfer/reserved-accounts/REF-001' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'accountReference' => 'REF-001',
                    'customerName' => 'Test Client'
                ]
            ]), 200)
        ]);

        $response = Monnify::ReservedAccounts()->getAccountDetails('REF-001');
        $this->assertEquals('REF-001', $response->accountReference);
    }
}
