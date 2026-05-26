<?php
// app/Jobs/ProcessWebhookJob.php

namespace App\Jobs;

use App\Models\WebhookCall;
use App\Models\WebhookFailure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;
    
    public WebhookCall $webhookCall;
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 minute, 5 minutes, 15 minutes
    
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }
    
    public function handle(): void
    {
        try {
            $payload = $this->webhookCall->payload;
            
            // Process different webhook types
            $eventType = $payload['event'] ?? 'unknown';
            
            Log::info('Processing webhook:', [
                'id' => $this->webhookCall->id,
                'event' => $eventType,
                'payload' => $payload
            ]);
            
            // Handle specific webhook events
            $this->handleWebhookEvent($eventType, $payload);
            
            // Mark as processed
            $this->webhookCall->markAsProcessed(200, ['status' => 'success']);
            
            // Store in separate log file
            $this->logToFile($payload);
            
            // Trigger real-time event
            event(new \App\Events\WebhookReceived($this->webhookCall));
            
        } catch (\Exception $e) {
            Log::error('Webhook processing failed:', [
                'id' => $this->webhookCall->id,
                'error' => $e->getMessage()
            ]);
            
            // Record failure
            WebhookFailure::create([
                'webhook_call_id' => $this->webhookCall->id,
                'error_message' => $e->getMessage(),
                'error_context' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
            
            $this->webhookCall->markAsFailed($e->getMessage());
            
            // Re-throw for retry
            throw $e;
        }
    }
    
    protected function handleWebhookEvent($eventType, $payload)
    {
        switch ($eventType) {
            case 'order.created':
                $this->handleOrderCreated($payload);
                break;
            case 'order.updated':
                $this->handleOrderUpdated($payload);
                break;
            case 'payment.received':
                $this->handlePaymentReceived($payload);
                break;
            default:
                Log::info("No specific handler for event: {$eventType}");
        }
    }
    
    protected function handleOrderCreated($payload)
    {
        // Custom logic for new orders
        Log::info("New order created: Order #{$payload['order_id']} - Amount: {$payload['amount']}");
        
        // You can add email notifications, database updates, etc.
    }
    
    protected function handleOrderUpdated($payload)
    {
        Log::info("Order updated: Order #{$payload['order_id']}");
    }
    
    protected function handlePaymentReceived($payload)
    {
        Log::info("Payment received: {$payload['amount']} for Order #{$payload['order_id']}");
    }
    
    protected function logToFile($payload)
    {
        $logData = [
            'timestamp' => now()->toDateTimeString(),
            'webhook_id' => $this->webhookCall->id,
            'payload' => $payload
        ];
        
        file_put_contents(
            storage_path('logs/webhook_' . date('Y-m-d') . '.log'),
            json_encode($logdata) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    public function failed(\Throwable $exception)
    {
        Log::error('Webhook job failed permanently after retries:', [
            'webhook_id' => $this->webhookCall->id,
            'error' => $exception->getMessage()
        ]);
        
        // Send admin notification
        // Mail::to('admin@example.com')->send(new WebhookFailedNotification($this->webhookCall, $exception));
    }
}