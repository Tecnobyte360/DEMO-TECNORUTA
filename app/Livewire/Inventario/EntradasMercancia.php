<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use App\Models\Inventario\EntradaMercancia;
use App\Models\Inventario\EntradaDetalle;
use App\Models\Productos\Producto;
use App\Models\SocioNegocio\SocioNegocio;
use App\Models\bodegas;
use Illuminate\Support\Facades\DB;
use Masmerise\Toaster\PendingToast;

class EntradasMercancia extends Component
{
    public $productos, $bodegas, $socios;
    public $entradas = [];
    public $fecha_contabilizacion, $socio_negocio_id, $lista_precio, $observaciones;
    public $entradasMercancia;
    public $detalleEntrada;
    public $isLoading = false;
    public $mostrarDetalle = false;

    public function mount()
    {
        $this->productos = Producto::where('activo', true)->get();
        $this->bodegas = bodegas::where('activo', true)->get();
        $this->socios = SocioNegocio::all();
        $this->entradasMercancia = EntradaMercancia::with('socioNegocio')->latest()->get();
        $this->entradas = [];
    }

    public function actualizarProductoDesdeNombre($index)
    {
        $nombre = $this->entradas[$index]['producto_nombre'] ?? null;

        if ($nombre) {
            $producto = Producto::where('nombre', $nombre)->first();

            if ($producto) {
                $this->entradas[$index]['producto_id'] = $producto->id;
                $this->entradas[$index]['descripcion'] = $producto->descripcion ?? $producto->nombre;

              
                $ultimaEntrada = \App\Models\Inventario\EntradaDetalle::where('producto_id', $producto->id)
                    ->latest('created_at')
                    ->first();

                if ($ultimaEntrada) {
                    $this->entradas[$index]['precio_unitario'] = $ultimaEntrada->precio_unitario;
                } else {
                    // Si no hay entrada previa, dejar en 0 o el valor actual
                    $this->entradas[$index]['precio_unitario'] = 0;
                }
            } else {
                $this->entradas[$index]['producto_id'] = null;
                $this->entradas[$index]['descripcion'] = '';
                $this->entradas[$index]['precio_unitario'] = 0;
            }
        }
    }

    public function render()
    {
        return view('livewire.inventario.entradas-mercancia');
    }

    public function agregarFila()
    {
        $this->entradas[] = [
            'producto_id' => '',
            'producto_nombre' => '',
            'descripcion' => '',
            'cantidad' => 1,
            'bodega_id' => '',
            'precio_unitario' => 0,
        ];
    }

    public function eliminarFila($index)
    {
        unset($this->entradas[$index]);
        $this->entradas = array_values($this->entradas);
    }

    public function updatedEntradas()
    {
        foreach ($this->entradas as $index => $entrada) {
            if (!empty($entrada['producto_id'])) {
                $producto = Producto::find($entrada['producto_id']);
                if ($producto) {
                    $this->entradas[$index]['descripcion'] = $producto->descripcion ?? $producto->nombre;
                }
            }
        }
    }

    public function crearEntrada()
    {
        $this->validate([
            'fecha_contabilizacion' => 'required|date',
            'socio_negocio_id' => 'required|exists:socio_negocios,id',
            'entradas.*.producto_id' => 'required|exists:productos,id',
            'entradas.*.bodega_id' => 'required|exists:bodegas,id',
            'entradas.*.cantidad' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        $this->isLoading = true;

        try {
            $entrada = EntradaMercancia::create([
                'socio_negocio_id' => $this->socio_negocio_id,
                'fecha_contabilizacion' => $this->fecha_contabilizacion,
                'lista_precio' => $this->lista_precio,
                'observaciones' => $this->observaciones,
            ]);

            foreach ($this->entradas as $prod) {
                EntradaDetalle::create([
                    'entrada_mercancia_id' => $entrada->id,
                    'producto_id' => $prod['producto_id'],
                    'bodega_id' => $prod['bodega_id'],
                    'cantidad' => $prod['cantidad'],
                    'precio_unitario' => $prod['precio_unitario'],
                ]);

                $producto = Producto::find($prod['producto_id']);
                if ($producto) {
                    $pivot = $producto->bodegas()->where('bodegas.id', $prod['bodega_id'])->first();

                    if ($pivot) {
                        $producto->bodegas()->updateExistingPivot($prod['bodega_id'], [
                            'stock' => DB::raw('stock + ' . $prod['cantidad'])
                        ]);
                    } else {
                        $producto->bodegas()->attach($prod['bodega_id'], [
                            'stock' => $prod['cantidad'],
                            'stock_minimo' => 0,
                            'stock_maximo' => null,
                        ]);
                    }

                    $nuevoStock = $producto->bodegas()->sum('producto_bodega.stock');
                    $producto->update(['stock' => $nuevoStock]);
                }
            }

            DB::commit();

            $this->reset(['fecha_contabilizacion', 'socio_negocio_id', 'lista_precio', 'observaciones', 'entradas']);
            $this->entradasMercancia = EntradaMercancia::with('socioNegocio')->latest()->get();

            PendingToast::create()
                ->success()
                ->message('✅ Entrada registrada exitosamente.')
                ->duration(5000);
        } catch (\Exception $e) {
            DB::rollBack();

            PendingToast::create()
                ->error()
                ->message('❌ Error al guardar: ' . $e->getMessage())
                ->duration(8000);
        } finally {
            $this->isLoading = false;
        }
    }
    public function cancelarEntrada()
    {
        $this->reset(['fecha_contabilizacion', 'socio_negocio_id', 'lista_precio', 'observaciones', 'entradas']);
    }

  public function verDetalle($entradaId)
{
    $entrada = EntradaMercancia::with('detalles.producto', 'detalles.bodega', 'socioNegocio')->find($entradaId);

    if ($entrada) {
        $this->detalleEntrada = $entrada;
        $this->mostrarDetalle = true;

        // 🔔 Dispara evento para Alpine
     $this->dispatch('abrirModalDetalle');
    } else {
        $this->detalleEntrada = null;
        $this->mostrarDetalle = false;

        PendingToast::create()
            ->error()
            ->message('Entrada no encontrada.')
            ->duration(5000);
    }
}

}
