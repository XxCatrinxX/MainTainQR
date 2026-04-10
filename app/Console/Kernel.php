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
        // Monitorear cambios de órdenes de servicio cada 30 segundos
        $schedule->job(\App\Jobs\MonitorOrderChangesJob::class)
                 ->everyThirtySeconds()
                 ->withoutOverlapping(120); // Evita ejecuciones simultáneas (timeout 2 min)

        // Alternativa: ejecutar cada minuto
        // $schedule->job(\App\Jobs\MonitorOrderChangesJob::class)
        //          ->everyMinute()
        //          ->withoutOverlapping();
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
