<?php

namespace HenryEjemuta\LaravelMonnify\Tests\Feature;

use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SubAccountsTest extends TestCase
{
    public function test_create_sub_account()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => ['accessToken' => 'access_token', 'expiresIn' => 3600]
            ]), 200),
            '*/api/v1/sub-accounts' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'subAccountCode' => 'MFY_SUB_12345',
                    'accountNumber' => '1234567890'
                ]
            ]), 200)
        ]);

        $response = Monnify::SubAccounts()->createSubAccount('001', '1234567890', 'sub@example.com');
        $this->assertEquals('MFY_SUB_12345', $response->subAccountCode);
    }

    public function test_delete_sub_account()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => ['accessToken' => 'access_token', 'expiresIn' => 3600]
            ]), 200),
            '*/api/v1/sub-accounts/MFY_SUB_12345' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => []
            ]), 200)
        ]);

        $response = Monnify::SubAccounts()->deleteSubAccount('MFY_SUB_12345');
        // If successful, response body is usually empty or specific success message.
        // Assuming deleteSubAccount returns responseBody which is [] here.
        $this->assertIsObject($response);
    }
}
