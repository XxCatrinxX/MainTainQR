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
        Schema::create('orden_servicio_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_servicio_id')->constrained('orden_servicios')->onDelete('cascade');
            $table->string('campo'); // 'estado', 'decision_cliente', 'fecha_entrega_real', etc
            $table->string('valor_anterior')->nullable();
            $table->string('valor_nuevo');
            $table->string('tipo_cambio'); // 'manual', 'sistema', 'cliente'
            $table->string('usuario_responsable')->nullable(); // Usuario que hizo el cambio
            $table->boolean('notificado')->default(false); // ¿Se notificó al cliente/técnico?
            $table->timestamp('fecha_notificacion')->nullable();
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('orden_servicio_id');
            $table->index('notificado');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_servicio_audits');
    }
};
