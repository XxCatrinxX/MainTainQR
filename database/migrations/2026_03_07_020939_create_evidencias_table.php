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
            $table->id('id_evidencia');

            $table->unsignedBigInteger('id_orden')
                ->constrained('ordenes_servicio', 'id_orden')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('id_usuario')
                ->constrained('usuarios', 'id_usuario');  
                
                
            $table->enum('tipo', [
                'diagnostico',
                'antes',
                'durante',
                'despues',
                'nota',
                'refacciones'
            ]);

            $table->string('ruta_foto')->nullable();
            $table->text('comentario')->nullable();
            $table->dateTime('fecha_registro')->useCurrent();
                

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
