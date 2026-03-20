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
        Schema::create('orden_repuestos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('orden_servicio_id')->constrained('orden_servicios');
    $table->foreignId('inventario_id')->constrained('inventario');
    $table->integer('cantidad')->default(1);
    $table->decimal('precio_fijado', 10, 2); // Precio al momento de la venta
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_repuestos');
    }
};
