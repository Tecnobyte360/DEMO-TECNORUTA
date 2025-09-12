<?php

namespace App\Models\SocioNegocio;

use Illuminate\Database\Eloquent\Model;

class SocioNegocio extends Model
{
    protected $fillable = [
        'razon_social',
        'nit',
        'telefono_fijo',
        'telefono_movil',
        'direccion',
        'correo',
        'municipio_barrio',
        'saldo_pendiente',
        'Tipo', // ← en BD está con T mayúscula
    ];

    protected $casts = [
        'Tipo' => 'string',
    ];

    /**
     * Alias en minúsculas para la columna `Tipo` (C=cliente, P=proveedor)
     * Así puedes usar $socio->tipo en el resto del código.
     */
    public function getTipoAttribute()
    {
        return isset($this->attributes['Tipo'])
            ? strtoupper(trim($this->attributes['Tipo']))
            : null;
    }

    public function setTipoAttribute($value)
    {
        // Si en algún punto haces $socio->tipo = 'C'/'P', guardará en `Tipo`
        $this->attributes['Tipo'] = strtoupper(trim($value));
    }

    /** Helpers */
    public function isCliente(): bool
    {
        return $this->tipo === 'C';
    }
    public function isProveedor(): bool
    {
        return $this->tipo === 'P';
    }

    /** Scopes útiles */
    public function scopeClientes($q)
    {
        return $q->where('Tipo', 'C');
    }
    public function scopeProveedores($q)
    {
        return $q->where('Tipo', 'P');
    }

    /** Relaciones */
    public function pedidos()
    {
        return $this->hasMany(\App\Models\Pedidos\Pedido::class, 'socio_negocio_id');
    }

    /** Atributo calculado: saldo pendiente de crédito */
    public function getSaldoPendienteCreditoAttribute()
    {
        return $this->pedidos
            ->where('tipo_pago', 'credito')
            ->sum(fn($pedido) => $pedido->montoPendiente());
    }

    /** Relación de pedidos de crédito en estado pendiente (si te sirve) */
    public function creditosPendientes()
    {
        return $this->hasMany(\App\Models\Pedidos\Pedido::class, 'socio_negocio_id')
            ->where('tipo_pago', 'credito')
            ->where('estado', 'pendiente');
    }

public function getNombreAttribute() { return $this->razon_social; }

}
