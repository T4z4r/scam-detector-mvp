<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Process feedback and retrain model daily at 2 AM
        $schedule->command('scam:process-feedback --auto-retrain')
                 ->daily()
                 ->at('02:00')
                 ->timezone('Africa/Nairobi')
                 ->runInBackground()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/scheduler.log'));
                 
        // Process feedback every 6 hours (without auto-retrain)
        $schedule->command('scam:process-feedback')
                 ->everySixHours()
                 ->timezone('Africa/Nairobi')
                 ->runInBackground()
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/scheduler.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}