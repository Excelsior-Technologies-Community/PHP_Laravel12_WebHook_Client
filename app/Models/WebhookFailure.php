<?php
// app/Models/WebhookFailure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookFailure extends Model
{
    protected $fillable = [
        'webhook_call_id', 'error_message', 'error_context', 'is_resolved'
    ];
    
    protected $casts = [
        'error_context' => 'array',
        'is_resolved' => 'boolean',
    ];
    
    public function webhookCall(): BelongsTo
    {
        return $this->belongsTo(WebhookCall::class);
    }
}