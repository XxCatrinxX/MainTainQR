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
    $table->id();
    $table->string('folio')->unique(); // Ej: OS-2026-0001
    $table->foreignId('equipo_id')->constrained('equipos');
    $table->foreignId('user_id')->constrained('users'); // Técnico asignado
    $table->text('falla_reportada');
    $table->text('estado_fisico');
    $table->enum('estado', ['recibido', 'diagnostico', 'espera', 'reparacion', 'listo', 'entregado'])->default('recibido');
    $table->enum('decision_cliente', ['pendiente', 'acepta', 'rechaza', 'venta_piezas'])->default('pendiente');
    $table->string('token_rastreo', 64)->unique(); // Acceso web sin login
    $table->dateTime('fecha_recepcion')->useCurrent();
    $table->dateTime('fecha_estimada_entrega')->nullable();
    $table->dateTime('fecha_reparacion')->nullable();
    $table->dateTime('fecha_entrega_real')->nullable();
    $table->decimal('mano_obra', 10, 2)->default(0);
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
