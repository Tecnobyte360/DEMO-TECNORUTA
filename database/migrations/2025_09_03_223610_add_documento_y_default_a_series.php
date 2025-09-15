<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('series')) {
            Schema::table('series', function (Blueprint $table) {
                if (!Schema::hasColumn('series', 'documento')) {
                    $table->string('documento', 40)->default('factura')->after('prefijo');
                }
                if (!Schema::hasColumn('series', 'es_default')) {
                    $table->boolean('es_default')->default(false)->after('documento');
                }
            });
        }

        // Elimina índice único previo si existía
        $exists = DB::selectOne("
            SELECT 1
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'series'
              AND INDEX_NAME   = 'IX_series_default_por_documento'
            LIMIT 1
        ");

        if ($exists) {
            DB::statement("ALTER TABLE `series` DROP INDEX `IX_series_default_por_documento`");
        }

        // Crea índice único en MySQL (sin filtro)
        DB::statement("ALTER TABLE `series` ADD UNIQUE `IX_series_default_por_documento` (`documento`, `es_default`)");
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `series` DROP INDEX `IX_series_default_por_documento`");
        } catch (\Throwable $e) {
            // ignorar si no existe
        }
    }
};
