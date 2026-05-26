<?php
// app/Http/Controllers/WebhookTestController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookTestController extends Controller
{
    public function receive(Request $request)
    {
        Log::info('Webhook received:', $request->all());
        
        file_put_contents(
            storage_path('logs/webhook_debug.log'),
            json_encode($request->all()) . PHP_EOL,
            FILE_APPEND
        );
        
        return response()->json([
            'status' => 'success',
            'message' => 'Webhook processed successfully',
            'data' => $request->all()
        ]);
    }
    
    public function testForm()
    {
        return view('webhooks.test');
    }
    
    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'webhook_url' => 'required|url',
            'event' => 'required|string',
            'payload' => 'required|array',
        ]);
        
        try {
            $response = Http::post($validated['webhook_url'], [
                'event' => $validated['event'],
                'timestamp' => now()->toISOString(),
                'data' => $validated['payload'],
            ]);
            
            return response()->json([
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response' => $response->json(),
                'message' => $response->successful() ? 'Webhook sent successfully!' : 'Webhook failed!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to send webhook!'
            ], 500);
        }
    }
    
    public function simulate(Request $request)
    {
        $eventType = $request->input('event_type', 'order.created');
        $customData = $request->input('custom_data', []);
        
        $testPayloads = [
            'order.created' => [
                'event' => 'order.created',
                'order_id' => rand(1000, 9999),
                'amount' => rand(100, 10000),
                'customer' => [
                    'name' => 'Test Customer',
                    'email' => 'test@example.com'
                ],
                'items' => [
                    ['id' => 1, 'name' => 'Product 1', 'quantity' => 2, 'price' => 50],
                    ['id' => 2, 'name' => 'Product 2', 'quantity' => 1, 'price' => 150],
                ]
            ],
            'order.updated' => [
                'event' => 'order.updated',
                'order_id' => rand(1000, 9999),
                'status' => 'shipped',
                'tracking_number' => 'TRK' . rand(100000, 999999)
            ],
            'payment.received' => [
                'event' => 'payment.received',
                'order_id' => rand(1000, 9999),
                'amount' => rand(100, 10000),
                'payment_method' => 'credit_card',
                'transaction_id' => 'TXN' . rand(100000, 999999)
            ]
        ];
        
        $payload = array_merge($testPayloads[$eventType] ?? $testPayloads['order.created'], $customData);
        
        // Send to local webhook endpoint
        $webhookUrl = url('/webhook-client');
        
        try {
            $response = Http::post($webhookUrl, $payload);
            
            return response()->json([
                'success' => $response->successful(),
                'payload_sent' => $payload,
                'response' => $response->json(),
                'status_code' => $response->status()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'payload_sent' => $payload,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}