<?php

namespace App\Models\Factura;

use App\Models\Factura\FacturaDetalle\FacturaDetalle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Serie\Serie;                               // ✅ namespace correcto
use App\Models\SocioNegocio\SocioNegocio;                 // ✅ cliente
             // ✅ detalles
use App\Models\Facturacion\FacturaPago;                   // ✅ pagos (si aún no existe, crea el modelo o comenta la relación)

class factura extends Model
{
    protected $table = 'facturas';

    protected $fillable = [
        'serie_id','numero','prefijo',
        'socio_negocio_id','cotizacion_id','pedido_id',
        'fecha','vencimiento','moneda',
        'tipo_pago','plazo_dias',
        'subtotal','impuestos','total','pagado','saldo',
        'estado','terminos_pago','notas','pdf_path',
    ];

    protected $casts = [
        'fecha'       => 'date',
        'vencimiento' => 'date',
        'subtotal'    => 'decimal:2',
        'impuestos'   => 'decimal:2',
        'total'       => 'decimal:2',
        'pagado'      => 'decimal:2',
        'saldo'       => 'decimal:2',
    ];

    /* ----------------- Relaciones ----------------- */

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class, 'serie_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(SocioNegocio::class, 'socio_negocio_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'factura_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(FacturaPago::class, 'factura_id');
    }

    /* --------------- Helpers de estado/pago --------------- */

    public function setContado(): self
    {
        $this->tipo_pago   = 'contado';
        $this->plazo_dias  = null;
        $this->vencimiento = $this->fecha ?: now()->toDateString();
        return $this;
    }

    public function setCredito(int $dias = 30): self
    {
        $this->tipo_pago   = 'credito';
        $this->plazo_dias  = $dias;
        $base              = $this->fecha ?: now();
        $this->vencimiento = Carbon::parse($base)->addDays($dias)->toDateString();
        return $this;
    }

    public function getVencidaAttribute(): bool
    {
        return $this->saldo > 0
            && $this->vencimiento
            && now()->toDateString() > $this->vencimiento->toDateString();
    }

    /** Número formateado con prefijo/longitud de la serie (si está disponible). */
    public function getNumeroFormateadoAttribute(): ?string
    {
        if (!$this->numero) return null;
        $len = $this->serie?->longitud ?? 6;
        $num = str_pad((string)$this->numero, $len, '0', STR_PAD_LEFT);
        return $this->prefijo ? "{$this->prefijo}-{$num}" : $num;
    }

    /* --------------- Operaciones de negocio --------------- */

    /** Agrega una línea y recalcula totales. $data coincide con fillable de FacturaDetalle. */
    public function agregarLinea(array $data): FacturaDetalle
    {
        $detalle = $this->detalles()->create($data);
        // Si tu modelo FacturaDetalle ya calcula importes en events, no hace falta llamar nada más.
        $this->recalcularTotales()->save();
        return $detalle;
    }

    /** Recalcula subtotal/impuestos/total/pagado/saldo y ajusta el estado (si no está anulada). */
    public function recalcularTotales(): self
    {
        $sub = (float) $this->detalles()->sum('importe_base');
        $imp = (float) $this->detalles()->sum('importe_impuesto');
        $tot = (float) $this->detalles()->sum('importe_total');

        $pag = (float) $this->pagos()->sum('monto');
        $sal = max($tot - $pag, 0);

        $this->subtotal  = $sub;
        $this->impuestos = $imp;
        $this->total     = $tot;
        $this->pagado    = $pag;
        $this->saldo     = $sal;

        if ($this->estado !== 'anulada') {
            if ($tot <= 0)       $this->estado = 'borrador';
            elseif ($sal <= 0)  $this->estado = 'pagada';
            elseif ($pag > 0)   $this->estado = 'parcialmente_pagada';
            else                $this->estado = 'emitida';
        }

        return $this;
    }

   
    public function registrarPago(array $data): FacturaPago
    {
        $pago = $this->pagos()->create($data);
        $this->recalcularTotales()->save();
        return $pago;
    }
}
