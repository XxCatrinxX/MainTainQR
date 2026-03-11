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
             $table->id('id_pago');

    $table->foreignId('id_orden')
        ->constrained('ordenes_servicio','id_orden');

    $table->decimal('monto',10,2);

    $table->string('metodo_pago',30);

    $table->enum('tipo_pago',[
        'cobro_cliente',
        'compra_equipo',
        'abono',
        'reembolso'
    ]);

    $table->enum('estado_pago',[
        'pendiente',
        'pagado',
        'cancelado'
    ])->default('pendiente');

    $table->dateTime('fecha_pago')->useCurrent();
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
