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
            $table->foreignId('cliente_id')->nullable()->after('equipo_id')->constrained('clientes');
        });

        // Backfill data from equipment to order
        \Illuminate\Support\Facades\DB::table('orden_servicios')
            ->join('equipos', 'orden_servicios.equipo_id', '=', 'equipos.id')
            ->update(['orden_servicios.cliente_id' => \Illuminate\Support\Facades\DB::raw('equipos.cliente_id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_servicios', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
        });
    }
};
