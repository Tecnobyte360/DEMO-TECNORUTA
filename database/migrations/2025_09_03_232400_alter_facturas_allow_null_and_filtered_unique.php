<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
{
    // Si vas a volver columnas nullable, puedes dejarlo; no es necesario borrar el índice
    if (Schema::hasTable('facturas')) {
        Schema::table('facturas', function (Blueprint $table) {
            if (Schema::hasColumn('facturas', 'serie_id')) {
                $table->unsignedBigInteger('serie_id')->nullable()->change();
            }
            if (Schema::hasColumn('facturas', 'numero')) {
                $table->unsignedBigInteger('numero')->nullable()->change();
            }
        });
    }

    // En MySQL: si el índice ya existe, NO lo toques
    $exists = DB::selectOne("
        SELECT 1
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = 'facturas'
          AND INDEX_NAME   = 'facturas_serie_id_numero_unique'
        LIMIT 1
    ");

    if (!$exists) {
        DB::statement("ALTER TABLE `facturas`
                       ADD UNIQUE `facturas_serie_id_numero_unique` (`serie_id`,`numero`)");
    }
}
   public function down(): void
{
    // No intentes borrar si hay FKs; puedes dejar vacío o intentar con try/catch
    try {
        DB::statement("ALTER TABLE `facturas` DROP INDEX `facturas_serie_id_numero_unique`");
    } catch (\Throwable $e) {}
}
};
