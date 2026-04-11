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
            $table->dateTime('fecha_diagnostico')->nullable()->after('fecha_recepcion');
            $table->dateTime('fecha_aprobacion')->nullable()->after('fecha_diagnostico');
            $table->dateTime('fecha_listo')->nullable()->after('fecha_reparacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_servicios', function (Blueprint $table) {
            $table->dropColumn(['fecha_diagnostico', 'fecha_aprobacion', 'fecha_listo']);
        });
    }
};
