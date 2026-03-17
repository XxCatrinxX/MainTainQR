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
        Schema::create('refaccion_productos', function (Blueprint $table) {
            $table->id('id_refaccion');

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('codigo', 100)->unique()->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();

            $table->decimal('precio', 10, 2);
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(0);

            $table->enum('estado', [
                'disponible',
                'agotado',
                'descontinuado'
            ])->default('disponible');

            $table->enum('tipo_pieza', [
                'original',
                'generica',
                'usada'
            ])->default('original');

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refaccion_productos');
    }
};
