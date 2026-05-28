<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookTestController;
use App\Http\Controllers\WebhookDashboardController;


Route::post('webhook-client', [WebhookTestController::class, 'receive']);

Route::prefix('webhooks')->group(function () {
    Route::get('/', [WebhookDashboardController::class, 'index'])->name('webhooks.dashboard');
    Route::get('/{id}', [WebhookDashboardController::class, 'show'])->name('webhooks.show');
    Route::post('/{id}/retry', [WebhookDashboardController::class, 'retry'])->name('webhooks.retry');
    Route::get('/export/data', [WebhookDashboardController::class, 'export'])->name('webhooks.export');
    Route::get('/stats/data', [WebhookDashboardController::class, 'stats'])->name('webhooks.stats');
    
    Route::get('/test/form', [WebhookTestController::class, 'testForm'])->name('webhooks.test.form');
    Route::post('/test/send', [WebhookTestController::class, 'sendTest'])->name('webhooks.test.send');
    Route::post('/test/simulate', [WebhookTestController::class, 'simulate'])->name('webhooks.test.simulate');
});