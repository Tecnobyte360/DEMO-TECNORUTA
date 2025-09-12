<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('serie_id');
            $table->unsignedBigInteger('numero'); // correlativo dentro de la serie
            $table->string('prefijo', 10);        // denormalizado para visualización/consulta rápida

            $table->unsignedBigInteger('socio_negocio_id');
            $table->unsignedBigInteger('cotizacion_id')->nullable();
            $table->unsignedBigInteger('pedido_id')->nullable();

            $table->date('fecha');
            $table->date('vencimiento')->nullable();

            $table->string('moneda', 8)->default('COP');

            // contado | credito
            $table->string('tipo_pago', 20)->default('contado');
            $table->unsignedSmallInteger('plazo_dias')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('impuestos', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->decimal('pagado', 14, 2)->default(0);
            $table->decimal('saldo', 14, 2)->default(0);

            // borrador | emitida | parcialmente_pagada | pagada | anulada
            $table->string('estado', 30)->default('borrador');

            $table->string('terminos_pago')->nullable();
            $table->text('notas')->nullable();

            $table->string('pdf_path')->nullable();

            $table->timestamps();

            // Unicidad por serie
            $table->unique(['serie_id', 'numero']);
            $table->index(['prefijo', 'numero']);
            $table->index(['estado', 'vencimiento']);

            $table->foreign('serie_id')->references('id')->on('series');
            $table->foreign('socio_negocio_id')->references('id')->on('socio_negocios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
