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