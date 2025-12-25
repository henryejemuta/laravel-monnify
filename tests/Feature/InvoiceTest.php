<?php

namespace HenryEjemuta\LaravelMonnify\Tests\Feature;

use HenryEjemuta\LaravelMonnify\Facades\Monnify;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class InvoiceTest extends TestCase
{
    public function test_create_invoice()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => ['accessToken' => 'access_token', 'expiresIn' => 3600]
            ]), 200),
            '*/api/v1/invoice/create' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'invoiceReference' => 'INV-001',
                    'invoiceUrl' => 'https://checkout.monnify.com/invoice/INV-001'
                ]
            ]), 200)
        ]);

        $paymentMethods = new \HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethods(
            \HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethod::CARD(),
            \HenryEjemuta\LaravelMonnify\Classes\MonnifyPaymentMethod::ACCOUNT_TRANSFER()
        );

        $response = Monnify::Invoice()->createAnInvoice(5000, '2025-12-30 12:00:00', 'Test Customer', 'customer@example.com', 'INV-001', 'Test Invoice', 'http://k.com', $paymentMethods);
        $this->assertEquals('INV-001', $response->invoiceReference);
    }

    public function test_cancel_invoice()
    {
        Http::fake([
            '*/api/v1/auth/login' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => ['accessToken' => 'access_token', 'expiresIn' => 3600]
            ]), 200),
            '*/api/v1/invoice/INV-001/cancel' => Http::response(json_encode([
                'requestSuccessful' => true,
                'responseMessage' => 'success',
                'responseCode' => '0',
                'responseBody' => [
                    'invoiceReference' => 'INV-001',
                    'status' => 'CANCELLED'
                ]
            ]), 200)
        ]);

        $response = Monnify::Invoice()->cancelAnInvoice('INV-001');
        $this->assertEquals('CANCELLED', $response->status);
    }
}
