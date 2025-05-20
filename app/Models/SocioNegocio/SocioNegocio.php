<?php

namespace App\Models\SocioNegocio;

use Illuminate\Database\Eloquent\Model;

class SocioNegocio extends Model
{
    protected $fillable = [
        'razon_social', 'nit', 'telefono_fijo', 'telefono_movil', 'direccion', 
        'correo', 'municipio_barrio', 'saldo_pendiente', 'Tipo'
    ];

}
