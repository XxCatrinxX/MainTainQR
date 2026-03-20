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
        Schema::create('pagos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('orden_servicio_id')->constrained('orden_servicios');
    $table->decimal('monto', 10, 2);
    $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia']);
    $table->enum('tipo_pago', ['anticipo', 'liquidacion']);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
