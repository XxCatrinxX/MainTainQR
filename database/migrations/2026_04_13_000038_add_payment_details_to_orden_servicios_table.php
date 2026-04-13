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
        Schema::table('orden_servicios', function (Blueprint $table) {
            $table->boolean('ofrecer_compra')->default(false)->after('monto_compra_piezas');
            $table->string('metodo_pago_compra', 50)->nullable()->after('ofrecer_compra');
            $table->text('datos_transferencia')->nullable()->after('metodo_pago_compra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_servicios', function (Blueprint $table) {
            $table->dropColumn(['ofrecer_compra', 'metodo_pago_compra', 'datos_transferencia']);
        });
    }
};
