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
        Schema::create('equipos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
    $table->string('tipo', 50); // Celular, Laptop
    $table->string('marca', 50);
    $table->string('modelo', 100);
    $table->string('numero_serie', 100)->unique();
    $table->string('qr_token')->unique(); // El que escanea el técnico
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
