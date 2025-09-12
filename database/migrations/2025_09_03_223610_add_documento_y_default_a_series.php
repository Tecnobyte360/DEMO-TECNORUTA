<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void
    {
        // Si usas esquema "admin", cambia 'series' por 'admin.series'
        Schema::table('series', function (Blueprint $table) {
            $table->string('documento', 40)->default('factura')->after('prefijo');
            $table->boolean('es_default')->default(false)->after('documento');
        });

        // (Opcional, SQL Server) Un único default por documento (índice filtrado)
        // Si usas esquema admin, pon [admin].[series]
        DB::statement('CREATE UNIQUE INDEX IX_series_default_por_documento ON [series] ([documento]) WHERE [es_default] = 1;');
    }

    public function down(): void
    {
        // Borra índice filtrado si lo creaste
        DB::statement('DROP INDEX IF EXISTS IX_series_default_por_documento ON [series];');

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn(['documento','es_default']);
        });
    }
};
