<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orden_servicios', function (Blueprint $table) {
            $table->id('id_orden');

            $table->foreignId('id_equipo')
                ->constrained('equipos', 'id_equipo')
                ->cascadeOnUpdate();

            $table->foreignId('id_usuario')
                ->constrained('usuario', 'id_usuario')
                ->cascadeOnUpdate();


            $table->text('problema_reportado');
            $table->text('diagnostico')->nullable();
            $table->text('actividad_a_realizar')->nullable();

            $table->enum('estado', [
                'abierta',
                'en_diagnostico',
                'en_cotizacion',
                'esperando_repuesta',
                'aprobada',
                'en_proceso',
                'cerrada',
                'cancelada'
            ])->default('abierta');

            $table->decimal('costo_materiales', 10, 2)->default(0);
            $table->decimal('costo_servicio', 10, 2)->default(0);
            $table->decimal('costo_total', 10, 2)->default(0);

            $table->dateTime('fecha_recepcion')->default(now());
            $table->dateTime('fecha_diagnostico')->nullable();
            $table->dateTime('fecha_cotizacion')->nullable();
            $table->dateTime('fecha_respuesta')->nullable();
            $table->dateTime('fecha_inicio_reparacion')->nullable();
            $table->dateTime('fecha_fin_reparacion')->nullable();

            $table->enum('decision_cliente', [
                'reparar',
                'retirar',
                'dejar_para_refacciones'
            ])->nullable();

            $table->decimal('monto_compra_equipo'. 10, 2)->nullable();
                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_servicios');
    }
};
