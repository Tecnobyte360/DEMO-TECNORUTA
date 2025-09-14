<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // Añade columnas solo si faltan (evita 1060 "duplicate column")
        Schema::table('series', function (Blueprint $table) {
            if (!Schema::hasColumn('series', 'documento')) {
                $table->string('documento', 40)->default('factura')->after('prefijo');
            }
            if (!Schema::hasColumn('series', 'es_default')) {
                $table->boolean('es_default')->default(false)->after('documento');
            }
        });

        $driver = DB::getDriverName();

        if ($driver === 'sqlsrv') {
            // Índice filtrado nativo en SQL Server
            DB::statement("
                IF NOT EXISTS (
                    SELECT 1 FROM sys.indexes WHERE name = 'IX_series_default_por_documento'
                    AND object_id = OBJECT_ID('series')
                )
                CREATE UNIQUE INDEX IX_series_default_por_documento
                ON [series] ([documento]) WHERE [es_default] = 1;
            ");
        } elseif ($driver === 'mysql') {
            // MySQL 8: índice ÚNICO FUNCIONAL (emula WHERE es_default=1)
            $exists = collect(DB::select("
                SHOW INDEX FROM `series` WHERE Key_name = 'ix_series_default_por_documento'
            "))->isNotEmpty();

            if (!$exists) {
                DB::statement("
                    CREATE UNIQUE INDEX `ix_series_default_por_documento`
                    ON `series` ((CASE WHEN `es_default` = 1 THEN `documento` ELSE NULL END))
                ");
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlsrv') {
            // Quita el índice filtrado
            DB::statement("
                IF EXISTS (
                    SELECT 1 FROM sys.indexes WHERE name = 'IX_series_default_por_documento'
                    AND object_id = OBJECT_ID('series')
                )
                DROP INDEX IX_series_default_por_documento ON [series];
            ");
        } elseif ($driver === 'mysql') {
            try {
                DB::statement("DROP INDEX `ix_series_default_por_documento` ON `series`");
            } catch (\Throwable $e) { /* ya no existe, ignorar */ }
        }

        // Elimina columnas si existen
        Schema::table('series', function (Blueprint $table) {
            if (Schema::hasColumn('series', 'es_default')) {
                $table->dropColumn('es_default');
            }
            if (Schema::hasColumn('series', 'documento')) {
                $table->dropColumn('documento');
            }
        });
    }
};
