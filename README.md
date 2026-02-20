# PHP_Laravel12_WebHook_Client

## Project Description

PHP_Laravel12_WebHook_Client is a Laravel 12 project designed to act as a Webhook Client. It can receive HTTP POST requests from external services, store the payload in the database, and log the data for processing.


## Key Features

- Receive webhooks via /webhook-client route.

- Save webhook calls in webhook_calls table using Spatie Laravel Webhook Client.

- Process webhook payload via ProcessWebhookJob.

- Logs payloads to laravel.log and optional separate log file.

- CSRF protection disabled for webhook route.

- Easy to test using Postman or any HTTP client.


## Technologies Used

- PHP 8.x – Backend programming language

- Laravel 12 – Web framework for routing, jobs, and database

- MySQL – Database to store webhook calls

- Spatie Webhook Client – Handles webhook requests and saves to DB

- Composer – Dependency management

- Postman – Tool to test webhook POST requests




---



## Installation Steps


---


## STEP 1: Create Laravel 12 Project

### Open terminal / CMD and run:

```
composer create-project laravel/laravel PHP_Laravel12_WebHook_Client "12.*"

```

### Go inside project:

```
cd PHP_Laravel12_WebHook_Client

```

#### Explanation:

Installs Laravel 12 and creates a project folder with all required files. 

The cd command navigates into the project directory.



## STEP 2: Database Setup (Optional)

### Open .env and set:

```
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:E5lu/Xb+LnzcefXtbiVevK5jCo7WBlLhfpSATHyN9Rk=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel12_webhook_client
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=sync
SESSION_DRIVER=file

WEBHOOK_CLIENT_SECRET=screte-key


```

### Create database in MySQL / phpMyAdmin:

```
Database name: laravel12_webhook_client

```

#### Explanation:

Connects the Laravel app to MySQL so it can save and retrieve webhook call data.



## STEP 3: Install Spatie Webhook Client

### Run:

```
composer require spatie/laravel-webhook-client

php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="config"

php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="migrations"

php artisan migrate

```


#### Explanation:

Installs the Spatie package, publishes config & migrations, and creates the database table for webhook calls.




## STEP 4: Update config/webhook-client.php

### config/webhook-client.php:


```
<?php

return [
    'configs' => [
        [
            'name' => 'default',

            // TEMPORARY: Accept all requests to avoid 500 error
            'signature_validator' => \Spatie\WebhookClient\SignatureValidator\AllowAllSignatureValidator::class,
            'signature_header_name' => 'Signature',
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => \App\Jobs\ProcessWebhookJob::class,
            'store_headers' => [],
        ],
    ],
];

```


#### Explanation:

Configures the webhook client, specifying how requests are validated, saved, and processed by a job.




## STEP 5: Create the Job

### Run:

```
php artisan make:job ProcessWebhookJob

```

### app/Jobs/ProcessWebhookJob.php:

```
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
        // Log payload immediately
        $payload = $this->webhookCall->payload;

        \Log::info('Webhook received:', $payload);

        // Optional: Save to separate file for debugging
        file_put_contents(storage_path('logs/webhook.log'), json_encode($payload) . PHP_EOL, FILE_APPEND);
    }
}

```


#### Explanation:

Handles processing of incoming webhook payloads and logs them safely for debugging or further processing.





## STEP 6: Disable CSRF for webhook route

### Run:

```
php artisan make:middleware VerifyCsrfToken

```


### app/Http/Middleware/VerifyCsrfToken.php

```
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'webhook-client', // disable CSRF for webhook
    ];
}

```


#### Explanation:

Prevents CSRF protection from blocking webhook POST requests to /webhook-client.




## STEP 7: Create the route

### routes/web.php:

```
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookTestController;

Route::webhooks('webhook-client');

Route::post('webhook-client', [WebhookTestController::class, 'receive']);

```


#### Explanation:

Defines the route for webhook calls; Spatie automatically saves calls to the database.





## STEP 8: Create Webhook Controller

### Run:

```
php artisan make:controller WebhookTestController

```

### app/Http/Controllers/WebhookTestController.php:

```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookTestController extends Controller
{
    public function receive(Request $request)
    {
        // Log payload immediately
        \Log::info('Webhook received:', $request->all());

        // Optional: separate log file
        file_put_contents(storage_path('logs/webhook_debug.log'), json_encode($request->all()) . PHP_EOL, FILE_APPEND);

        return response()->json(['status' => 'success']);
    }
}

```


#### Explanation:

Optional controller to test logging of webhook payloads without relying on Spatie.





## STEP 9: Test in Postman


1. Method: POST

2. URL: http://127.0.0.1:8000/webhook-client

3. Headers: 

```
Content-Type: application/json

```

4. Body (raw JSON):

```
{
  "event": "order.created",
  "order_id": 123,
  "amount": 500
}

```

5. Send request → response:

```
{"status":"success"}

```


#### Explanation:

Tests webhook endpoint; payload should now be saved to the database and logged.





## So you can see this type Output:


<img width="1447" height="905" alt="Screenshot 2026-02-20 103707" src="https://github.com/user-attachments/assets/0ce5175f-96ee-46b5-8ae5-6beac6a24ef5" />




---

# Project FOLDER Structure:

```

PHP_Laravel12_WebHook_Client/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── WebhookTestController.php
│   │   ├── Middleware/
│   │   │   └── VerifyCsrfToken.php
│   │   └── Kernel.php
│   ├── Jobs/
│   │   └── ProcessWebhookJob.php
│   ├── Models/
│   └── Providers/
├── bootstrap/
│   └── app.php
├── config/
│   ├── app.php
│   └── webhook-client.php
├── database/
│   ├── factories/
│   ├── migrations/
│   │   └── create_webhook_calls_table.php
│   └── seeders/
├── public/
│   └── index.php
├── resources/
│   ├── views/
│   └── js/ 
├── routes/
│   └── web.php
├── storage/
│   └── logs/
│       ├── laravel.log
│       └── webhook.log
├── tests/
├── vendor/
├── artisan
├── composer.json
├── composer.lock
└── .env

```
