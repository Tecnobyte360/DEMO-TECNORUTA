<?php

namespace App\Models\Factura;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class facturaPago extends Model
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
