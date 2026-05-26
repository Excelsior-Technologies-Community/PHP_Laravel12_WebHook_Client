// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Cleanup old webhooks every day at midnight
    $schedule->command('webhooks:cleanup --days=30')->daily();
    
    // Retry failed webhooks every hour
    $schedule->command('webhooks:retry-failed')->hourly();
}