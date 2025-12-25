<?php

namespace HenryEjemuta\LaravelMonnify\Tests\Feature;

use HenryEjemuta\LaravelMonnify\Events\NewWebHookCallReceived;
use HenryEjemuta\LaravelMonnify\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class WebhookEventTest extends TestCase
{
    public function test_webhook_event_is_dispatched()
    {
        Event::fake();

        $payload = json_encode([
            'eventData' => [
                'transactionReference' => 'TRX-001',
                'paymentReference' => 'PAY-001',
                'amountPaid' => '5000',
                'totalPayable' => '5000',
                'paidOn' => '2025-12-25 12:00:00',
                'paymentStatus' => 'PAID',
                'paymentDescription' => 'Test Payment',
                'currency' => 'NGN',
                'paymentMethod' => 'CARD'
            ],
            'eventType' => 'SUCCESSFUL_TRANSACTION'
        ]);

        // Simulate a webhook call (this depends on how the package handles webhooks, 
        // usually via a Controller or a helper that dispatches the event)
        // Since I don't see a controller in the file list earlier, I'll assume usage of the Event class directly
        // or check if there is a route.
        // Checking README, it mentions "The Laravel Monnify already include all required webhook endpoints".
        // And "transaction completion https://your_domain/laravel-monnify/webhook/transaction-completion"
        
        // I'll try to post to that route.
        
        $response = $this->postJson('/laravel-monnify/webhook/transaction-completion', json_decode($payload, true), ['monnify-signature' => 'test_signature']);

        // If the package registers routes automatically (which it seems to via ServiceProvider), this should work.
        // However, I need to ensure routes are loaded in test environment.
        // The TestCase doesn't seem to load routes explicitly but ServiceProvider might.
        
        // Let's inspect ServiceProvider.php later if this fails.
        // For now, I will assume the route exists.
        
        // Wait, the README says "NewWebHookCallReceived" is the event.
        
        // If route fails, I'll update the test to manually instantiate the event to prove it works,
        // but prefer testing the route integration.
        
        // To permit the route, I might need to disable CSRF or similar if it's not excluded, 
        // but package routes usually are API routes.
        
        // Let's just try dispatching the event manually to verify the Event class itself works as expected
        // if likely the route setup needs more config in Testbench.
        
        // Actually, let's try the route.
        
        $response->assertStatus(200);
        
        Event::assertDispatched(NewWebHookCallReceived::class, function ($e) use ($payload) {
            // Check properties of event
            return $e->webHookCall->transactionReference === 'TRX-001';
        });
    }
}
