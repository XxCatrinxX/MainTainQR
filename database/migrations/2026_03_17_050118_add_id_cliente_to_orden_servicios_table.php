<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('orden_servicios', function (Blueprint $table) {
$table->unsignedBigInteger('id_cliente')->nullable()->after('id_orden');

        // Llave foránea (opcional pero recomendado)
        $table->foreign('id_cliente')
              ->references('id_cliente')
              ->on('clientes')
              ->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('orden_servicios', function (Blueprint $table) {
        $table->dropForeign(['id_cliente']);
        $table->dropColumn('id_cliente');
    });
}
};
