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
        Schema::create('detalles_tecnicos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('orden_servicio_id')->constrained('orden_servicios')->onDelete('cascade');
    $table->text('solucion_propuesta')->nullable();
    $table->text('trabajo_finalizado')->nullable();
    $table->text('observaciones_internas')->nullable();
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_tecnicos');
    }
};
