<?php

namespace App\Http\Controllers;

use App\Models\WebhookCall;
use App\Models\WebhookFailure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WebhookDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = WebhookCall::query();

        if ($request->filled('event')) {
            $query->where('name', 'like', '%' . $request->event . '%');
        }

        if ($request->filled('order_id')) {
            $query->where('payload->order_id', $request->order_id);
        }

        if ($request->filled('amount')) {
            $query->where('payload->amount', $request->amount);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $webhooks = $query->latest()->paginate(15);

        $stats = [
            'total' => WebhookCall::count(),
            'today' => WebhookCall::whereDate('created_at', today())->count(),
            'failed' => WebhookCall::where('status', 'failed')->count(),
            'pending' => WebhookCall::where('status', 'pending')->count(),
            'success_rate' => $this->getSuccessRate(),
            'avg_response_time' => $this->getAverageResponseTime(),
            'events_by_type' => $this->getEventsByType(),
            'recent_failures' => WebhookFailure::with('webhookCall')
                ->where('is_resolved', false)
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('webhooks.dashboard', compact('webhooks', 'stats'));
    }

    public function show($id)
    {
        $webhook = WebhookCall::with('failures')->findOrFail($id);
        return view('webhooks.show', compact('webhook'));
    }

    public function retry($id)
    {
        $webhook = WebhookCall::findOrFail($id);
        
        if ($webhook->canRetry()) {
            $webhook->incrementRetryCount();
            $webhook->update(['status' => 'pending']);
            
            dispatch(new \App\Jobs\ProcessWebhookJob($webhook));
            
            return redirect()->back()->with('success', 'Webhook retry scheduled successfully!');
        }
        
        return redirect()->back()->with('error', 'Maximum retry attempts reached!');
    }

    public function getTest()
    {
        return view('webhooks.test');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'payload' => 'required|json'
        ]);

        try {
            $response = Http::post($request->url, json_decode($request->payload, true));
            return back()->with('success', 'Test Request Sent! Status: ' . $response->status());
        } catch (\Exception $e) {
            return back()->with('error', 'Test Failed: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $query = WebhookCall::query();
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $webhooks = $query->get();
        
        $filename = 'webhooks_export_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp', 'w');
        
        fputcsv($handle, ['ID', 'Event', 'Payload', 'Status', 'Created At']);
        
        foreach ($webhooks as $webhook) {
            fputcsv($handle, [
                $webhook->id,
                $webhook->name,
                json_encode($webhook->payload),
                $webhook->status,
                $webhook->created_at
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    public function stats()
    {
        $stats = [
            'total_by_hour' => $this->getWebhooksByHour(),
            'success_rate' => $this->getSuccessRate(),
            'top_events' => $this->getTopEvents(),
            'failures_by_type' => $this->getFailuresByType(),
        ];
        
        return response()->json($stats);
    }

    protected function getAverageResponseTime()
    {
        $avg = WebhookCall::whereNotNull('response_code')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, processed_at)) as average'))
            ->value('average');
        
        return round($avg ?? 0, 2);
    }

    protected function getEventsByType()
    {
        $events = WebhookCall::select('name', DB::raw('count(*) as total'))
            ->groupBy('name')
            ->pluck('total', 'name')
            ->toArray();
        
        return !empty($events) ? $events : ['No events' => 0];
    }

    protected function getWebhooksByHour()
    {
        $data = [];
        for ($i = 0; $i < 24; $i++) {
            $data[$i] = WebhookCall::whereHour('created_at', $i)->count();
        }
        return $data;
    }

    protected function getSuccessRate()
    {
        $total = WebhookCall::count();
        if ($total === 0) {
            return 100;
        }
        
        $successful = WebhookCall::where('status', 'processed')->count();
        return round(($successful / $total) * 100, 2);
    }

    protected function getTopEvents($limit = 5)
    {
        return WebhookCall::select('name', DB::raw('count(*) as total'))
            ->groupBy('name')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();
    }

    protected function getFailuresByType()
    {
        return WebhookFailure::select('error_message', DB::raw('count(*) as total'))
            ->groupBy('error_message')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
    }
}