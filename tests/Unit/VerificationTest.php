<?php

namespace HenryEjemuta\LaravelMonnify\Tests\Unit;

use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class VerificationTest extends TestCase
{
    public function test_validate_bvn()
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
            '*/api/v1/vas/bvn-details-match' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'name' => [
                        'matchStatus' => 'FULL_MATCH',
                        'field' => 'name'
                    ]
                ]
            ]), 200)
        ]);

        $response = Monnify::Verification()->validateBVN('12345678901', 'Test User', '01-01-1990', '08012345678');
        $this->assertNotNull($response);
        $this->assertEquals('FULL_MATCH', $response->name->matchStatus);
    }

    public function test_validate_bvn_account_match()
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
            '*/api/v1/vas/bvn-account-match' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'matchStatus' => 'FULL_MATCH'
                ]
            ]), 200)
        ]);

        $response = Monnify::Verification()->validateBVNAccountInvalidation('12345678901', '0000000000', '058');
        $this->assertNotNull($response);
        $this->assertEquals('FULL_MATCH', $response->matchStatus);
    }

    public function test_validate_nin()
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
            '*/api/v1/vas/nin-details' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'nin' => '12345678901'
                ]
            ]), 200)
        ]);

        $response = Monnify::Verification()->validateNIN('12345678901');
        $this->assertNotNull($response);
        $this->assertEquals('12345678901', $response->nin);
    }
}
