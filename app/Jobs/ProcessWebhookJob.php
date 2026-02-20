<?php

namespace App\Jobs;

use Spatie\WebhookClient\Models\WebhookCall;

class ProcessWebhookJob
{
    public WebhookCall $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle(): void
    {
        // Safely log payload
        $payload = $this->webhookCall->payload;

        // Optional logging
        \Log::info('Webhook processed:', $payload);
    }
}