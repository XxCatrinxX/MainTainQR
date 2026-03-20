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
        Schema::create('evidencias', function (Blueprint $table) {
    $table->id();
    $table->foreignId('orden_servicio_id')->constrained('orden_servicios');
    $table->string('url_foto');
    $table->enum('momento', ['recepcion', 'diagnostico', 'reparacion', 'finalizado']);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidencias');
    }
};
