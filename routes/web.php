<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookTestController;

Route::webhooks('webhook-client');

Route::post('webhook-client', [WebhookTestController::class, 'receive']);