<?php
// app/Events/WebhookReceived.php

namespace App\Events;

use App\Models\WebhookCall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public WebhookCall $webhookCall;
    
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }
    
    public function broadcastOn()
    {
        return new Channel('webhooks');
    }
    
    public function broadcastWith()
    {
        return [
            'id' => $this->webhookCall->id,
            'name' => $this->webhookCall->name,
            'payload' => $this->webhookCall->payload,
            'created_at' => $this->webhookCall->created_at->toISOString(),
        ];
    }
}