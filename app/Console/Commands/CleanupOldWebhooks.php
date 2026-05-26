<?php
// app/Console/Commands/CleanupOldWebhooks.php

namespace App\Console\Commands;

use App\Models\WebhookCall;
use Illuminate\Console\Command;

class CleanupOldWebhooks extends Command
{
    protected $signature = 'webhooks:cleanup {--days=30 : Delete webhooks older than X days}';
    protected $description = 'Clean up old webhook records';
    
    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);
        
        $deleted = WebhookCall::where('created_at', '<', $date)
            ->where('status', 'processed')
            ->delete();
        
        $this->info("Deleted {$deleted} old webhook records.");
    }
}