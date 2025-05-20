<?php

namespace App\Livewire\MaestroRutas;

use Livewire\Component;
use App\Models\User;
use App\Models\Ruta\Ruta;
use App\Models\Vehiculo\Vehiculo;
use App\Models\Productos\Producto;
use App\Models\Productos\ProductoBodega;
use App\Models\bodegas;
use App\Models\InventarioRuta\InventarioRuta;
use Livewire\WithPagination;
class MaestroRutas extends Component
{
    public $rutas;
    public $vehiculos;
    public $conductores;
    public $productos;
    public $bodegas;
    public $inventarioVista = [];
    public $rutaVistaId = null;

    public $vehiculo_id;
    public $ruta;
    public $fecha_salida;
    public $conductor_ids = [];
    public $ruta_id;
    public $isEdit = false;

    public $producto_id;
    public $bodega_id;
    public $cantidad_asignada;
    public $stockDisponible = null;
    public $asignaciones = [];

    public $erroresFormulario = false;

    public function mount()
    {
        $this->vehiculos = Vehiculo::all();
        $this->conductores = User::all();
        $this->productos = Producto::all();
        $this->bodegas = bodegas::all();
        $this->cargarRutas();
    }

    public function cargarRutas()
    {
        $this->rutas = Ruta::with(['vehiculo', 'conductores'])->latest()->get();
    }
        public function guardarRuta()
        {
            $this->resetErrorBag();
            $this->erroresFormulario = false;

            // Validación principal del formulario
            $this->validate([
                'vehiculo_id' => 'required|exists:vehiculos,id',
                'ruta' => 'required|string|max:255',
                'fecha_salida' => 'required|date',
                'conductor_ids' => 'required|array|min:1',
            ]);

            // Validar que haya al menos una asignación
            if (empty($this->asignaciones)) {
                $this->erroresFormulario = true;
                $this->dispatch('error', ['mensaje' => 'Debe asignar al menos un producto a la ruta antes de guardarla.']);
                return;
            }

            // Validar cada asignación (producto, bodega y cantidad válidos)
            foreach ($this->asignaciones as $index => $item) {
                if (
                    empty($item['producto_id']) ||
                    empty($item['bodega_id']) ||
                    empty($item['cantidad']) ||
                    !is_numeric($item['cantidad']) ||
                    $item['cantidad'] <= 0
                ) {
                    $this->erroresFormulario = true;
                    $this->dispatch('error', [
                        'mensaje' => "La asignación #".($index + 1)." es inválida. Verifique producto, bodega y cantidad."
                    ]);
                    return;
                }
            }

            // Crear la ruta
            $ruta = Ruta::create([
                'vehiculo_id' => $this->vehiculo_id,
                'ruta' => $this->ruta,
                'fecha_salida' => $this->fecha_salida,
            ]);

            // Asociar conductores
            $ruta->conductores()->sync($this->conductor_ids);

            // Guardar asignaciones e impactar stock
            foreach ($this->asignaciones as $item) {
                InventarioRuta::create([
                    'ruta_id' => $ruta->id,
                    'producto_id' => $item['producto_id'],
                    'bodega_id' => $item['bodega_id'],
                    'cantidad' => $item['cantidad'],
                ]);

                $pb = ProductoBodega::where('producto_id', $item['producto_id'])
                    ->where('bodega_id', $item['bodega_id'])
                    ->first();

                if ($pb && $pb->stock >= $item['cantidad']) {
                    $pb->decrement('stock', $item['cantidad']);
                }
            }

            // Reset y feedback
            $this->resetFormulario();
            $this->cargarRutas();
            $this->dispatch('ruta-creada', ['mensaje' => 'Ruta registrada exitosamente.']);
        }



   public function actualizarRuta()
{
    $this->validate([
        'vehiculo_id' => 'required|exists:vehiculos,id',
        'ruta' => 'required|string|max:255',
        'fecha_salida' => 'required|date',
        'conductor_ids' => 'required|array|min:1',
    ]);

    $ruta = Ruta::with('inventarios')->findOrFail($this->ruta_id);

    // Revertir el stock antes de eliminar asignaciones anteriores
    foreach ($ruta->inventarios as $inventario) {
        $pb = ProductoBodega::where('producto_id', $inventario->producto_id)
            ->where('bodega_id', $inventario->bodega_id)
            ->first();

        if ($pb) {
            $pb->increment('stock', $inventario->cantidad); // Devolver stock anterior
        }

        $inventario->delete();
    }

    // Actualizar la ruta y conductores
    $ruta->update([
        'vehiculo_id' => $this->vehiculo_id,
        'ruta' => $this->ruta,
        'fecha_salida' => $this->fecha_salida,
    ]);
    $ruta->conductores()->sync($this->conductor_ids);

    // Registrar las nuevas asignaciones y ajustar el stock
    foreach ($this->asignaciones as $item) {
        InventarioRuta::create([
            'ruta_id' => $ruta->id,
            'producto_id' => $item['producto_id'],
            'bodega_id' => $item['bodega_id'],
            'cantidad' => $item['cantidad'],
        ]);

        $pb = ProductoBodega::where('producto_id', $item['producto_id'])
            ->where('bodega_id', $item['bodega_id'])
            ->first();

        if ($pb && $pb->stock >= $item['cantidad']) {
            $pb->decrement('stock', $item['cantidad']);
        }
    }

    $this->resetFormulario();
    $this->cargarRutas();
    $this->dispatch('ruta-actualizada', ['mensaje' => 'Ruta actualizada correctamente.']);
}


    public function updatedProductoId()
    {
        $this->consultarStock();
    }

    public function updatedBodegaId()
    {
        $this->consultarStock();
    }

    public function consultarStock()
    {
        $this->stockDisponible = null;

        if ($this->producto_id && $this->bodega_id) {
            $productoBodega = ProductoBodega::where('producto_id', $this->producto_id)
                ->where('bodega_id', $this->bodega_id)
                ->first();

            $this->stockDisponible = $productoBodega?->stock ?? 0;
        }
    }

   public function agregarAsignacion()
{
    // Validación explícita
    $this->validate([
        'producto_id' => 'required|exists:productos,id',
        'bodega_id' => 'required|exists:bodegas,id',
        'cantidad_asignada' => 'required|numeric|min:1',
    ]);

    $this->consultarStock();

    if ($this->stockDisponible === null || $this->stockDisponible <= 0) {
        $this->dispatch('error', ['mensaje' => 'Stock insuficiente en la bodega seleccionada.']);
        return;
    }

    if ($this->cantidad_asignada > $this->stockDisponible) {
        $this->dispatch('error', ['mensaje' => 'La cantidad excede el stock disponible.']);
        return;
    }

    $producto = Producto::find($this->producto_id);
    $bodega = bodegas::find($this->bodega_id);

    $this->asignaciones[] = [
        'producto_id' => $this->producto_id,
        'producto_nombre' => $producto->nombre,
        'bodega_id' => $this->bodega_id,
        'bodega_nombre' => $bodega->nombre,
        'cantidad' => $this->cantidad_asignada,
    ];

    // Reset campos individuales
    $this->reset(['producto_id', 'bodega_id', 'cantidad_asignada', 'stockDisponible']);
}


    public function eliminarAsignacion($index)
    {
        unset($this->asignaciones[$index]);
        $this->asignaciones = array_values($this->asignaciones);
    }

    public function edit($id)
    {
        $ruta = Ruta::with(['conductores', 'inventarios.producto', 'inventarios.bodega'])->findOrFail($id);

        $this->vehiculo_id = $ruta->vehiculo_id;
        $this->ruta = $ruta->ruta;
        $this->fecha_salida = $ruta->fecha_salida;
        $this->conductor_ids = $ruta->conductores->pluck('id')->toArray();
        $this->ruta_id = $ruta->id;
        $this->isEdit = true;

        $this->asignaciones = $ruta->inventarios->map(function ($inv) {
            return [
                'producto_id' => $inv->producto_id,
                'producto_nombre' => $inv->producto->nombre,
                'bodega_id' => $inv->bodega_id,
                'bodega_nombre' => $inv->bodega->nombre,
                'cantidad' => $inv->cantidad,
            ];
        })->toArray();
    }

    public function eliminar($id)
    {
        Ruta::findOrFail($id)->delete();
        $this->cargarRutas();
        $this->dispatch('ruta-eliminada', ['mensaje' => 'Ruta eliminada correctamente.']);
    }

    public function resetFormulario()
    {
        $this->reset([
            'vehiculo_id', 'ruta', 'fecha_salida', 'conductor_ids', 'ruta_id', 'isEdit',
            'producto_id', 'bodega_id', 'cantidad_asignada', 'stockDisponible', 'asignaciones'
        ]);
        $this->resetErrorBag();
    }

    public function verInventario($rutaId)
    {
        if ($this->rutaVistaId === $rutaId) {
            $this->rutaVistaId = null;
            $this->inventarioVista = [];
            return;
        }

        $this->rutaVistaId = $rutaId;

        $inventario = InventarioRuta::with(['producto', 'bodega'])
            ->where('ruta_id', $rutaId)
            ->get();

        $this->inventarioVista = $inventario->map(function ($item) {
            return [
                'producto' => $item->producto->nombre ?? '-',
                'bodega' => $item->bodega->nombre ?? '-',
                'cantidad' => $item->cantidad,
            ];
        })->toArray();
    }

public function updated($propertyName)
{
    $this->validateOnly($propertyName, [
        'vehiculo_id' => 'required|exists:vehiculos,id',
        'ruta' => 'required|string|max:255',
        'fecha_salida' => 'required|date',
        'conductor_ids' => 'required|array|min:1',
        'producto_id' => 'required|exists:productos,id',
        'bodega_id' => 'required|exists:bodegas,id',
        'cantidad_asignada' => 'required|numeric|min:1',
    ]);
}





     public function render()
    {
        return view('livewire.maestro-rutas.maestro-rutas', [
            'rutas' => Ruta::with(['vehiculo', 'conductores'])->latest()->paginate(5)
        ]);
    }
}
