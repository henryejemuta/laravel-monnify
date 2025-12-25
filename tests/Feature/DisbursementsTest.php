<?php

namespace HenryEjemuta\LaravelMonnify\Tests\Feature;

use HenryEjemuta\LaravelMonnify\Classes\MonnifyBankAccount;
use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class DisbursementsTest extends TestCase
{
    public function test_initiate_transfer_single()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => ['accessToken' => 'access_token', 'expiresIn' => 3600]
            ]), 200),
            '*/api/v1/disbursements/single' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'amount' => 5000,
                    'reference' => 'TXN-001',
                    'status' => 'PENDING'
                ]
            ]), 200)
        ]);

        $bankAccount = new MonnifyBankAccount('1234567890', '001', 'Test Account');
        $response = Monnify::Disbursements()->initiateTransferSingle(5000, 'TXN-001', 'Payment', $bankAccount);
        
        $this->assertEquals('TXN-001', $response->reference);
        $this->assertEquals(5000, $response->amount);
    }
}
