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
        Schema::create('inventario', function (Blueprint $table) {
    $table->id();
    $table->string('nombre_pieza', 150);
    $table->string('sku')->unique();
    $table->enum('calidad', ['original', 'generica', 'usada']);
    $table->integer('stock')->default(0);
    $table->decimal('precio_venta', 10, 2);
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
