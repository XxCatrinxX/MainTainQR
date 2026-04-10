<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\MonitorOrderChangesJob;

class MonitorOrderChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:monitor {--immediate : Ejecutar inmediatamente en lugar de encolar}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Monitorea cambios en órdenes de servicio y envía notificaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('immediate')) {
            $this->info('Ejecutando monitoreo de órdenes...');
            
            try {
                $job = new MonitorOrderChangesJob();
                $job->handle(app('App\Services\FirebaseNotificationService'));
                $this->info('✓ Monitoreo completado exitosamente');
            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
                return 1;
            }
        } else {
            $this->info('Encolando Job de monitoreo...');
            dispatch(new MonitorOrderChangesJob());
            $this->info('✓ Job encolado correctamente');
        }

        return 0;
    }
}
