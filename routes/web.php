<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookTestController;
use App\Http\Controllers\WebhookDashboardController;

Route::webhooks('webhook-client');

Route::get('/webhooks', [WebhookDashboardController::class, 'index']);