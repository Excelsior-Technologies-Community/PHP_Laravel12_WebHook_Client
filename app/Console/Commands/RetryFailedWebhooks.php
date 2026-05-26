<?php
// app/Console/Commands/RetryFailedWebhooks.php

namespace App\Console\Commands;

use App\Models\WebhookCall;
use App\Jobs\ProcessWebhookJob;
use Illuminate\Console\Command;

class RetryFailedWebhooks extends Command
{
    protected $signature = 'webhooks:retry-failed';
    protected $description = 'Retry all failed webhooks';
    
    public function handle()
    {
        $failedWebhooks = WebhookCall::where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->get();
        
        foreach ($failedWebhooks as $webhook) {
            $webhook->incrementRetryCount();
            $webhook->update(['status' => 'pending']);
            dispatch(new ProcessWebhookJob($webhook));
            $this->info("Queued retry for webhook #{$webhook->id}");
        }
        
        $this->info("Retried {$failedWebhooks->count()} failed webhooks.");
    }
}