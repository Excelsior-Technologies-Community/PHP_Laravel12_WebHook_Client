<?php

return [
    'configs' => [
        [
            'name' => 'default',

            'signing_secret' => env('WEBHOOK_CLIENT_SECRET'),

            'signature_validator' => App\AllowAllSignatureValidator::class,

            'signature_header_name' => 'Signature',

            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,

            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            'process_webhook_job' => \App\Jobs\ProcessWebhookJob::class,

            'store_headers' => [],
        ],
    ],
];
