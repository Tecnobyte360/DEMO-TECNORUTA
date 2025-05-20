<?php

namespace App\Models\Ruta;

use App\Models\InventarioRuta\InventarioRuta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Vehiculo\Vehiculo;

class Ruta extends Model
{
    use HasFactory;

    protected $table = 'rutas';

    protected $fillable = [
        'vehiculo_id',
        'ruta',
        'fecha_salida',

    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

   public function conductores()
{
    return $this->belongsToMany(\App\Models\User::class, 'conductor_ruta', 'ruta_id', 'user_id');
}
public function inventarios()
{
    return $this->hasMany(InventarioRuta::class);
}

}
