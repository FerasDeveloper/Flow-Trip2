<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
   
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $notificationService = app()->make(\App\Services\NotificationService::class);
            $notificationService->send_flight_reminders();
        })->timezone('Asia/Damascus')->dailyAt('00:58');
    }

    
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
