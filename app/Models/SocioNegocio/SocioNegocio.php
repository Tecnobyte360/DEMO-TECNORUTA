<?php

namespace App\Models\SocioNegocio;

use Illuminate\Database\Eloquent\Model;

class SocioNegocio extends Model
{
    protected $fillable = [
        'razon_social', 'nit', 'telefono_fijo', 'telefono_movil', 'direccion', 
        'correo', 'municipio_barrio', 'saldo_pendiente', 'Tipo'
    ];

  public function pedidos()
    {
        return $this->hasMany(\App\Models\Pedidos\Pedido::class, 'socio_negocio_id');
    }
    public function getSaldoPendienteCreditoAttribute()
{
    return $this->pedidos
        ->where('tipo_pago', 'credito')
        ->sum(function ($pedido) {
            return $pedido->montoPendiente();
        });
}
public function creditosPendientes()
{
    return $this->hasMany(\App\Models\Pedidos\Pedido::class, 'socio_negocio_id')
                ->where('tipo_pago', 'credito')
                ->where('estado', 'pendiente');
}

}
