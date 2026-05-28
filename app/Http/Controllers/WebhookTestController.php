<?php

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
            'secret_key' => 'nullable|string'
        ]);
        
        $payload = [
            'event' => $validated['event'],
            'timestamp' => now()->toISOString(),
            'data' => $validated['payload'],
        ];

        $headers = ['X-Timestamp' => $payload['timestamp']];
        
        if (!empty($validated['secret_key'])) {
            $headers['X-Signature'] = hash_hmac('sha256', json_encode($payload), $validated['secret_key']);
        }
        
        try {
            $response = Http::withHeaders($headers)->post($validated['webhook_url'], $payload);
            
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
        $delay = $request->input('delay', 0);
        
        $testPayloads = [
            'order.created' => [
                'event' => 'order.created',
                'order_id' => rand(1000, 9999),
                'amount' => rand(100, 10000),
                'customer' => ['name' => 'Test Customer', 'email' => 'test@example.com']
            ],
            'order.updated' => [
                'event' => 'order.updated',
                'order_id' => rand(1000, 9999),
                'status' => 'shipped'
            ],
            'payment.received' => [
                'event' => 'payment.received',
                'order_id' => rand(1000, 9999),
                'amount' => rand(100, 10000),
                'transaction_id' => 'TXN' . rand(100000, 999999)
            ]
        ];
        
        $payload = array_merge($testPayloads[$eventType] ?? $testPayloads['order.created'], $customData);
        
        $webhookUrl = url('/webhook-client');
        
        try {
            if ($delay > 0) {
                sleep((int)$delay);
            }
            
            $response = Http::post($webhookUrl, $payload);
            
            return response()->json([
                'success' => $response->successful(),
                'payload_sent' => $payload,
                'response' => $response->json(),
                'status_code' => $response->status(),
                'latency' => $response->transferStats ? $response->transferStats->getTransferTime() : 'N/A'
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