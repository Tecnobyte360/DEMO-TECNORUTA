<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) agrega la columna (nullable+default para no romper filas existentes)
        Schema::table('series', function (Blueprint $table) {
            $table->unsignedTinyInteger('longitud')->nullable()->default(6)->after('proximo');
        });

        // 2) rellena las filas existentes
        DB::table('series')->whereNull('longitud')->update(['longitud' => 6]);

        // 3) (opcional) vuelve la columna NOT NULL (SQL Server)
        DB::statement('ALTER TABLE [series] ALTER COLUMN [longitud] TINYINT NOT NULL');
    }

    public function down(): void
    {
        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('longitud');
        });
    }
};