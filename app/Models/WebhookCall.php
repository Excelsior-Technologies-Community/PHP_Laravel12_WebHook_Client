<?php
// app/Models/WebhookCall.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookCall extends Model
{
    protected $table = 'webhook_calls';
    
    protected $fillable = [
        'name', 'url', 'headers', 'payload', 'exception',
        'status', 'retry_count', 'processed_at', 'response_code', 'response_body'
    ];
    
    protected $casts = [
        'headers' => 'array',
        'payload' => 'array',
        'response_body' => 'array',
        'processed_at' => 'datetime',
    ];
    
    public function failures(): HasMany
    {
        return $this->hasMany(WebhookFailure::class, 'webhook_call_id');
    }
    
    public function markAsProcessed($responseCode = null, $responseBody = null)
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
            'response_code' => $responseCode,
            'response_body' => $responseBody,
        ]);
    }
    
    public function markAsFailed($exception = null)
    {
        $this->update([
            'status' => 'failed',
            'exception' => $exception,
        ]);
    }
    
    public function incrementRetryCount()
    {
        $this->increment('retry_count');
    }
    
    public function canRetry($maxRetries = 3)
    {
        return $this->status === 'failed' && $this->retry_count < $maxRetries;
    }
}