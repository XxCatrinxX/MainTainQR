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
            $table->id('id_equipo');

            $table->foreignId('id_cliente')
                ->constrained('clientes', 'id_cliente')
                ->cascadeOnUpdate();

            $table->string('tipo_equipo', 50);
            $table->string('marca', 80);
            $table->string('modelo', 100);
            $table->string('num_serie', 100)->unique();
            $table->string('color', 50)->nullable();
            $table->string('observaciones', 255)->nullable();
            $table->string('qr_code')->nullable();

            /*$table->enum('estado_equipo', [ 
                'activo',
                'para_refacciones',
                'en_reparacion',
                'reparado',
                'baja'
            ])->default('activo');*/

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
