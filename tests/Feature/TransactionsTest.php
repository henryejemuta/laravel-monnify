<?php

namespace HenryEjemuta\LaravelMonnify\Tests\Feature;

use HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethod;
use HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethods;
use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class TransactionsTest extends TestCase
{
    public function test_initialize_transaction()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => ['accessToken' => 'access_token', 'expiresIn' => 3600]
            ]), 200),
            '*/api/v1/merchant/transactions/init-transaction' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'transactionReference' => 'TRX-001',
                    'paymentReference' => 'PAY-001',
                    'checkoutUrl' => 'https://checkout.monnify.com/pay/PAY-001'
                ]
            ]), 200)
        ]);

        $paymentMethods = new MonnifyPaymentMethods(MonnifyPaymentMethod::CARD(), MonnifyPaymentMethod::ACCOUNT_TRANSFER());
        $response = Monnify::Transactions()->initializeTransaction(1000, 'User', 'user@example.com', 'TRX-001', 'Desc', 'http://k.com', $paymentMethods);
        
        $this->assertEquals('TRX-001', $response->transactionReference);
    }

    public function test_get_transaction_status()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => ['accessToken' => 'access_token', 'expiresIn' => 3600]
            ]), 200),
            '*/api/v2/transactions/TRX-001/' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'transactionReference' => 'TRX-001',
                    'paymentStatus' => 'PAID'
                ]
            ]), 200)
        ]);

        $response = Monnify::Transactions()->getTransactionStatus('TRX-001');
        $this->assertEquals('PAID', $response->paymentStatus);
    }
}
