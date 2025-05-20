<?php

namespace App\Livewire\Productos;

use App\Models\bodegas;
use Livewire\Component;
use App\Models\Productos\Producto;
use App\Models\Categorias\Subcategoria;

class Productos extends Component
{
    public $productos;
    public $subcategorias;
    public $bodegas;
    public $mostrarBodegas = [];
    // Datos del producto
    public $nombre, $descripcion, $costo, $precio, $activo = true, $subcategoria_id;
    public $producto_id;
    public $isEdit = false;
    public $search = '';

    // Para manejar bodegas
    public $bodegaSeleccionada = '';
    public $stockMinimo = 0;
    public $stockMaximo = null;
    public $stocksPorBodega = [];

    // Stock Global
    public $stockMinimoGlobal;
    public $stockMaximoGlobal;

    //validaciones
    public $erroresFormulario = false;


    public function mount()
    {
        $this->productos = collect();
        $this->subcategorias = Subcategoria::all();
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
            $this->dispatch('error', ['mensaje' => 'Selecciona una bodega primero.']);
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
                'costo' => 'required|numeric|min:0',
                'activo' => 'required|boolean',
                'stockMinimoGlobal' => 'nullable|integer|min:0',
                'stockMaximoGlobal' => 'nullable|integer|min:0|gte:stockMinimoGlobal',
            ], [
                'nombre.required' => 'El nombre es obligatorio.',
                'nombre.max' => 'El nombre no debe tener más de 255 caracteres.',
                'descripcion.max' => 'La descripción no debe tener más de 500 caracteres.',
                'subcategoria_id.required' => 'Debes seleccionar una subcategoría.',
                'precio.required' => 'El precio es obligatorio.',
                'precio.numeric' => 'El precio debe ser un número.',
                'precio.min' => 'El precio debe ser mayor o igual a cero.',
                'costo.required' => 'El costo es obligatorio.',
                'costo.numeric' => 'El costo debe ser un número.',
                'costo.min' => 'El costo debe ser mayor o igual a cero.',
                'activo.required' => 'El estado activo/inactivo es obligatorio.',
                'stockMinimoGlobal.integer' => 'El stock mínimo global debe ser un número entero.',
                'stockMaximoGlobal.integer' => 'El stock máximo global debe ser un número entero.',
                'stockMaximoGlobal.gte' => 'El stock máximo debe ser mayor o igual al stock mínimo.',
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
                'costo' => $this->costo,
                'precio' => $this->precio,
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
            $this->dispatch('producto-creado', ['mensaje' => 'Producto creado exitosamente.']);
        } catch (\Exception $e) {
            $this->dispatch('error', ['mensaje' => 'Ocurrió un error al guardar el producto: ' . $e->getMessage()]);
        }
    }
    
    


    public function updated($propertyName)
{
    $this->validateOnly($propertyName, [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string|max:500',
        'subcategoria_id' => 'required|exists:subcategorias,id',
        'precio' => 'required|numeric|min:0',
        'costo' => 'required|numeric|min:0',
        'activo' => 'required|boolean',
        'stockMinimoGlobal' => 'nullable|integer|min:0',
        'stockMaximoGlobal' => 'nullable|integer|min:0|gte:stockMinimoGlobal',
    ]);
}


    


    public function update()
    {
        $this->validate([
            'nombre' => 'required|string',
            'subcategoria_id' => 'required|exists:subcategorias,id',
            'precio' => 'required|numeric',
            'costo' => 'required|numeric',
        ]);

        // Aplicar stock global si está definido
        $this->aplicarStockGlobalSiExiste();

        $producto = Producto::findOrFail($this->producto_id);

        $producto->update([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'costo' => $this->costo,
            'precio' => $this->precio,
            'activo' => $this->activo,
            'subcategoria_id' => $this->subcategoria_id,
        ]);

        // Actualizar las bodegas también si fuera necesario (opcional)
        foreach ($this->stocksPorBodega as $bodegaId => $stockData) {
            $producto->bodegas()->syncWithoutDetaching([
                $bodegaId => [
                    'stock_minimo' => $stockData['stock_minimo'] ?? 0,
                    'stock_maximo' => $stockData['stock_maximo'] ?? null,
                ]
            ]);
        }

        $this->resetInput();

        $this->dispatch('producto-actualizado', ['mensaje' => 'Producto actualizado exitosamente.']);
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
            'nombre', 'descripcion', 'costo', 'precio', 'activo', 'subcategoria_id',
            'producto_id', 'isEdit', 'bodegaSeleccionada', 'stockMinimo', 'stockMaximo',
            'stocksPorBodega', 'stockMinimoGlobal', 'stockMaximoGlobal'
        ]);
    }

    public function edit($id)
{
    $producto = Producto::with('bodegas')->findOrFail($id);

    $this->producto_id = $producto->id;
    $this->nombre = $producto->nombre;
    $this->descripcion = $producto->descripcion;
    $this->costo = $producto->costo;
    $this->precio = $producto->precio;
    $this->activo = $producto->activo;
    $this->subcategoria_id = $producto->subcategoria_id;
    $this->isEdit = true;

    // Cargar stock por bodega del producto
    $this->stocksPorBodega = [];
    foreach ($producto->bodegas as $bodega) {
        $this->stocksPorBodega[$bodega->id] = [
            'stock_minimo' => $bodega->pivot->stock_minimo,
            'stock_maximo' => $bodega->pivot->stock_maximo,
        ];
    }
}

}
