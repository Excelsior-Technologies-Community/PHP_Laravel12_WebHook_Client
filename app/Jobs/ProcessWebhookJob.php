<?php

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
    public $backoff =;
    
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }
    
    public function handle(): void
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            $payload = $this->webhookCall->payload;
            $eventType = $payload['event'] ?? 'unknown';
            
            $this->handleWebhookEvent($eventType, $payload);
            
            $executionTime = round(microtime(true) - $startTime, 4) . 's';
            $memoryUsage = round((memory_get_usage() - $startMemory) / 1024, 2) . ' KB';

            $this->webhookCall->update([
                'status' => 'success',
                'execution_time' => $executionTime,
                'memory_usage' => $memoryUsage
            ]);

            $this->logToFile($payload);
            event(new \App\Events\WebhookReceived($this->webhookCall));
            
        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 4) . 's';
            
            WebhookFailure::create([
                'webhook_call_id' => $this->webhookCall->id,
                'error_message' => $e->getMessage(),
                'error_context' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
            
            $this->webhookCall->update(['status' => 'failed', 'execution_time' => $executionTime]);
            
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
        }
    }
    
    protected function handleOrderCreated($payload)
    {
    }
    
    protected function handleOrderUpdated($payload)
    {
    }
    
    protected function handlePaymentReceived($payload)
    {
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
            json_encode($logData) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    public function failed(\Throwable $exception)
    {
        Log::error('Webhook job failed permanently:', [
            'webhook_id' => $this->webhookCall->id,
            'error' => $exception->getMessage()
        ]);
    }
}