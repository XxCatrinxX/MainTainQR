<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orden_servicios', function (Blueprint $table) {
            $table->boolean('es_reparable')->nullable()->after('solucion_propuesta');
            $table->decimal('monto_compra_piezas', 10, 2)->nullable()->after('mano_obra');
        });

        // Solo si tu columna estado es ENUM en MySQL y de verdad quieres agregar para_pzas
        DB::statement("
            ALTER TABLE orden_servicios 
            MODIFY estado ENUM(
                'recibido',
                'diagnostico',
                'espera',
                'reparacion',
                'para_pzas',
                'listo',
                'entregado'
            ) NOT NULL DEFAULT 'recibido'
        ");
    }

    public function down(): void
    {
        // Si agregaste para_pzas, primero mueve esos registros a otro estado válido
        DB::table('orden_servicios')
            ->where('estado', 'para_pzas')
            ->update(['estado' => 'espera']);

        Schema::table('orden_servicios', function (Blueprint $table) {
            $table->dropColumn(['es_reparable', 'monto_compra_piezas']);
        });

        DB::statement("
            ALTER TABLE orden_servicios 
            MODIFY estado ENUM(
                'recibido',
                'diagnostico',
                'espera',
                'reparacion',
                'listo',
                'entregado'
            ) NOT NULL DEFAULT 'recibido'
        ");
    }
};