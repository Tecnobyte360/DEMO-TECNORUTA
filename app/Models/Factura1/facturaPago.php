<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaPago extends Model
{
    protected $table = 'factura_pagos';

    protected $fillable = [
        'factura_id',
        'fecha',
        'metodo',
        'monto',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Factura\factura::class, 'factura_id');
    }
}
