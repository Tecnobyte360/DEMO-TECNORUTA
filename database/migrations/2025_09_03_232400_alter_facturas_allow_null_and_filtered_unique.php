<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    // cambia a '[facturas]' si estás en dbo, o a '[dbo].[facturas]'
    private string $table = 'facturas';

    public function up(): void
    {
        // 0) Elimina el índice único existente (Laravel lo llamó así por defecto)
        DB::statement("
IF EXISTS (
  SELECT 1 FROM sys.indexes 
  WHERE name = 'facturas_serie_id_numero_unique'
    AND object_id = OBJECT_ID('{$this->table}')
)
BEGIN
  DROP INDEX [facturas_serie_id_numero_unique] ON {$this->table};
END
        ");

        // 1) Permitir NULL en columnas que el borrador deja vacías
        DB::statement("ALTER TABLE {$this->table} ALTER COLUMN [numero]  BIGINT NULL;");
        DB::statement("ALTER TABLE {$this->table} ALTER COLUMN [prefijo] NVARCHAR(10) NULL;");
        DB::statement("ALTER TABLE {$this->table} ALTER COLUMN [serie_id] BIGINT NULL;");

        // 2) Índice único FILTRADO: aplica solo cuando hay numero
        DB::statement("
IF NOT EXISTS (
  SELECT 1 FROM sys.indexes 
  WHERE name = 'UX_facturas_serie_numero_notnull'
    AND object_id = OBJECT_ID('{$this->table}')
)
BEGIN
  CREATE UNIQUE INDEX [UX_facturas_serie_numero_notnull]
    ON {$this->table} ([serie_id], [numero])
    WHERE [numero] IS NOT NULL;
END
        ");
    }

    public function down(): void
    {
        // Quitar índice filtrado
        DB::statement("
IF EXISTS (
  SELECT 1 FROM sys.indexes 
  WHERE name = 'UX_facturas_serie_numero_notnull'
    AND object_id = OBJECT_ID('{$this->table}')
)
BEGIN
  DROP INDEX [UX_facturas_serie_numero_notnull] ON {$this->table};
END
        ");

        // Volver a NOT NULL (solo si realmente quisieras revertir)
        DB::statement("ALTER TABLE {$this->table} ALTER COLUMN [numero]  BIGINT NOT NULL;");
        DB::statement("ALTER TABLE {$this->table} ALTER COLUMN [prefijo] NVARCHAR(10) NOT NULL;");
        DB::statement("ALTER TABLE {$this->table} ALTER COLUMN [serie_id] BIGINT NOT NULL;");

        // Restaurar el índice único “normal”
        DB::statement("
CREATE UNIQUE INDEX [facturas_serie_id_numero_unique]
  ON {$this->table} ([serie_id], [numero]);
        ");
    }
};
