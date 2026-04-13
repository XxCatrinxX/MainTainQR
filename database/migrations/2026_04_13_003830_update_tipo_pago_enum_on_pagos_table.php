<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // En MySQL, cambiar de enum a string se puede hacer crudo para evitar problemas con dbal
        DB::statement("ALTER TABLE pagos MODIFY COLUMN tipo_pago VARCHAR(255) NOT NULL DEFAULT 'liquidacion'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE pagos MODIFY COLUMN tipo_pago ENUM('anticipo', 'liquidacion') NOT NULL DEFAULT 'liquidacion'");
    }
};
