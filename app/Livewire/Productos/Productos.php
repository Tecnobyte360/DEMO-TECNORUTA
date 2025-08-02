<?php

namespace App\Livewire\Productos;

use Livewire\Component;
use App\Models\bodegas;
use App\Models\Productos\Producto;
use App\Models\Categorias\Subcategoria;
use Illuminate\Support\Facades\Log;
use Masmerise\Toaster\PendingToast;

class Productos extends Component
{
    public $productos, $subcategorias, $bodegas, $mostrarBodegas = [];
    public $nombre, $descripcion, $costo, $precio, $activo = true, $subcategoria_id;
    public $producto_id, $isEdit = false, $search = '';

    public $bodegaSeleccionada = '';
    public $stockMinimo = 0, $stockMaximo = null;
    public $stocksPorBodega = [];

    public $stockMinimoGlobal, $stockMaximoGlobal;
    public $erroresFormulario = false;

    public function mount()
    {
        $this->productos = collect();
        $this->subcategorias = Subcategoria::where('activo', true)->get();
        $this->bodegas = bodegas::where('activo', true)->get();
    }

    public function render()
    {
        $query = Producto::with('subcategoria', 'bodegas');

        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }

        $this->productos = $query->get();

        return view('livewire.productos.productos');
    }

    public function agregarBodega()
    {
        if (!$this->bodegaSeleccionada) {
            PendingToast::create()->error()->message('Selecciona una bodega primero.')->duration(4000);
            return;
        }

        $this->stocksPorBodega[$this->bodegaSeleccionada] = [
            'stock_minimo' => $this->stockMinimo,
            'stock_maximo' => $this->stockMaximo,
        ];

        $this->bodegaSeleccionada = '';
        $this->stockMinimo = 0;
        $this->stockMaximo = null;
    }

    public function eliminarBodega($id)
    {
        unset($this->stocksPorBodega[$id]);
    }

    public function store()
    {
        try {
            $this->erroresFormulario = false;

            $this->validate([
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:500',
                'subcategoria_id' => 'required|exists:subcategorias,id',
                'precio' => 'required|numeric|min:0',
                'activo' => 'required|boolean',
                'stockMinimoGlobal' => 'nullable|integer|min:0',
                'stockMaximoGlobal' => 'nullable|integer|min:0|gte:stockMinimoGlobal',
            ]);

            if (Producto::where('nombre', $this->nombre)->exists()) {
                $this->addError('nombre', 'Ya existe un producto registrado con este nombre.');
                $this->erroresFormulario = true;
                return;
            }

            $this->aplicarStockGlobalSiExiste();

            $producto = Producto::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                   'costo' => $this->costo ?? 0, 
                'stock' => 0,
                'stock_minimo' => 0,
                'stock_maximo' => null,
                'activo' => $this->activo,
                'subcategoria_id' => $this->subcategoria_id,
            ]);

            foreach ($this->stocksPorBodega as $bodegaId => $stockData) {
                $producto->bodegas()->attach($bodegaId, [
                    'stock' => 0,
                    'stock_minimo' => $stockData['stock_minimo'] ?? 0,
                    'stock_maximo' => $stockData['stock_maximo'] ?? null,
                ]);
            }

            $this->resetInput();

            PendingToast::create()->success()->message('Producto creado exitosamente.')->duration(5000);
        } catch (\Throwable $e) {
            Log::error('Error al guardar producto', ['message' => $e->getMessage()]);
            PendingToast::create()->error()->message('Error al guardar el producto: ' . $e->getMessage())->duration(9000);
        }
    }

    public function update()
    {
        try {
            $this->validate([
                'nombre' => 'required|string',
                'subcategoria_id' => 'required|exists:subcategorias,id',
                'precio' => 'required|numeric',
            ]);

            $this->aplicarStockGlobalSiExiste();

            $producto = Producto::findOrFail($this->producto_id);

            $producto->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio' => $this->precio,
                'activo' => $this->activo,
                'subcategoria_id' => $this->subcategoria_id,
            ]);

            foreach ($this->stocksPorBodega as $bodegaId => $stockData) {
                $producto->bodegas()->syncWithoutDetaching([
                    $bodegaId => [
                        'stock_minimo' => $stockData['stock_minimo'] ?? 0,
                        'stock_maximo' => $stockData['stock_maximo'] ?? null,
                    ]
                ]);
            }

            $this->resetInput();

            PendingToast::create()->success()->message('Producto actualizado exitosamente.')->duration(5000);
        } catch (\Throwable $e) {
            Log::error('Error al actualizar producto', [
                'id' => $this->producto_id,
                'message' => $e->getMessage(),
            ]);

            PendingToast::create()->error()->message('Error al actualizar el producto.')->duration(8000);
        }
    }

    public function edit($id)
    {
        try {
            $producto = Producto::with('bodegas')->findOrFail($id);

            $this->producto_id = $producto->id;
            $this->nombre = $producto->nombre;
            $this->descripcion = $producto->descripcion;
            $this->precio = $producto->precio;

            $this->activo = (bool) $producto->activo;
            $this->subcategoria_id = $producto->subcategoria_id;
            $this->isEdit = true;

            $this->stocksPorBodega = [];

            foreach ($producto->bodegas as $bodega) {
                $this->stocksPorBodega[$bodega->id] = [
                    'stock_minimo' => $bodega->pivot->stock_minimo,
                    'stock_maximo' => $bodega->pivot->stock_maximo,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Error al cargar producto para edición', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);

            PendingToast::create()->error()->message('Error al cargar el producto.')->duration(7000);
        }
    }


    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'subcategoria_id' => 'required|exists:subcategorias,id',
            'precio' => 'required|numeric|min:0',
            'activo' => 'required|boolean',
            'stockMinimoGlobal' => 'nullable|integer|min:0',
            'stockMaximoGlobal' => 'nullable|integer|min:0|gte:stockMinimoGlobal',
        ]);
    }

    private function aplicarStockGlobalSiExiste()
    {
        if (!is_null($this->stockMinimoGlobal) || !is_null($this->stockMaximoGlobal)) {
            foreach ($this->bodegas as $bodega) {
                $this->stocksPorBodega[$bodega->id]['stock_minimo'] = $this->stockMinimoGlobal ?? 0;
                $this->stocksPorBodega[$bodega->id]['stock_maximo'] = $this->stockMaximoGlobal ?? null;
            }
        }
    }

    private function resetInput()
    {
        $this->reset([
            'nombre',
            'descripcion',
            'precio',
            'activo',
            'subcategoria_id',
            'producto_id',
            'isEdit',
            'bodegaSeleccionada',
            'stockMinimo',
            'stockMaximo',
            'stocksPorBodega',
            'stockMinimoGlobal',
            'stockMaximoGlobal'
        ]);
    }
}
