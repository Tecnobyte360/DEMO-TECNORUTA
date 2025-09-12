<?php

namespace App\Models\Productos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categorias\Subcategoria;
use App\Models\bodegas;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'costo',
        'precio',
        'activo',
        'subcategoria_id',
        'es_articulo_compra',
        'es_articulo_venta',
        'impuesto_id'
    ];

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class);
    }

    public function bodegas()
    {
        return $this->belongsToMany(bodegas::class, 'producto_bodega', 'producto_id', 'bodega_id')
            ->withPivot('stock', 'stock_minimo', 'stock_maximo')
            ->withTimestamps();
    }

    public function precios()
    {
        return $this->hasMany(PrecioProducto::class);
    }

public function impuesto()
{
    return $this->belongsTo(\App\Models\Impuestos\Impuesto::class, 'impuesto_id');
}
public function getPrecioConIvaAttribute(): float
{
    $base = (float) $this->precio;
    $imp  = $this->impuesto;

    if (!$imp) return round($base, 2);

    if ($imp->porcentaje !== null) {
        return round($base * (1 + ((float)$imp->porcentaje / 100)), 2);
    }

    if ($imp->monto_fijo !== null) {
        return round($base + (float)$imp->monto_fijo, 2);
    }

    return round($base, 2);
}
}
