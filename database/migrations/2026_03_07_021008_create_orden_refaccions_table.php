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
        Schema::create('orden_refaccions', function (Blueprint $table) {
            $table->id('id_orden_refaccion');

            $table->foreignId('id_orden')
                ->constrained('orden_servicios', 'id_orden')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->text('descripcion_refaccion');
            $table->decimal('costo_estimado', 10, 2)->nullable();
            $table->decimal('costo_real', 10, 2)->nullable();

            $table->enum('estado', [
                'pendiente',
                'solicitada',
                'aprobada',
                'entregada',
                'rechazada',
                'cancelada'
            ])->default('pendiente');

            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->dateTime('fecha_entrega')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_refaccions');
    }
};
