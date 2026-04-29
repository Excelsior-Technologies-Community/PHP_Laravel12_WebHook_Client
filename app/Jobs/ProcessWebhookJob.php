<?php

namespace App\Jobs;

use Spatie\WebhookClient\Jobs\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessWebhookJob extends SpatieProcessWebhookJob
{
    public function handle(): void
    {
        $payload = $this->webhookCall->payload;

        \Log::info('Webhook processed:', $payload);
    }
}