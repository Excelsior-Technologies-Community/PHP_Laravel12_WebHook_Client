<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\WebhookClient\Models\WebhookCall;

class WebhookDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = WebhookCall::query();

        // 1. Search by event name (column)
        if ($request->filled('event')) {
            $query->where('name', 'like', '%' . $request->event . '%');
        }

        // 2. Search by order_id (inside JSON payload)
        if ($request->filled('order_id')) {
            $query->where('payload->order_id', $request->order_id);
        }

        // 3. Search by amount (inside JSON payload)
        if ($request->filled('amount')) {
            $query->where('payload->amount', $request->amount);
        }

        $webhooks = $query->latest()->paginate(10);

        return view('webhooks.index', compact('webhooks'));
    }
}  