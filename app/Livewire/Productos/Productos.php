<?php

namespace App\Livewire\Productos;

use Livewire\Component;
use App\Models\bodegas;
use App\Models\Productos\Producto;
use App\Models\Categorias\Subcategoria;
use App\Models\Impuestos\Impuesto as ImpuestoModel;
use Illuminate\Support\Facades\Log;
use Masmerise\Toaster\PendingToast;

class Productos extends Component
{
    /** Listas */
    public $productos, $subcategorias, $bodegas, $impuestos;

    /** Campos producto */
    public $nombre, $descripcion, $costo, $precio, $activo = true, $subcategoria_id;
    public ?int $impuesto_id = null;

    /** Estado edición */
    public $producto_id, $isEdit = false, $search = '';

    /** Stocks por bodega */
    public $bodegaSeleccionada = '';
    public $stockMinimo = 0, $stockMaximo = null;
    public $stocksPorBodega = [];

    /** Stock global opcional */
    public $stockMinimoGlobal, $stockMaximoGlobal;

    /** Flags UI */
    public $mostrarBodegas = [];
    public $erroresFormulario = false;

    public function mount()
    {
        $this->productos      = collect();
        $this->subcategorias  = Subcategoria::where('activo', true)->orderBy('nombre')->get();
        $this->bodegas        = bodegas::where('activo', true)->orderBy('nombre')->get();

        // Cargar impuestos activos que aplican a VENTAS o AMBOS (ej. IVA)
        $this->impuestos = ImpuestoModel::with('tipo')
            ->where('activo', true)
           
            ->orderBy('prioridad')->orderBy('nombre')
            ->get();
    }

 public function render()
{
    $query = Producto::with(['subcategoria','bodegas','impuesto']);
    if ($this->search) $query->where('nombre', 'like', '%'.$this->search.'%');
    $this->productos = $query->get();

    // Preview en vivo para la fila que estás editando (opcional)
    if ($this->isEdit && $this->producto_id) {
        $this->productos = $this->productos->map(function ($p) {
            if ($p->id === (int) $this->producto_id) {
                $p->precio = (float) ($this->precio ?? $p->precio);
                // “inyecta” el impuesto seleccionado para que el accessor use este valor
                if ($this->impuestoSeleccionado) {
                    $p->setRelation('impuesto', $this->impuestoSeleccionado);
                }
            }
            return $p;
        });
    }

    return view('livewire.productos.productos', ['impuestos' => $this->impuestos]);
}
    /** ====== Bodegas por producto (UI) ====== */
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

    /** ====== Crear ====== */
    public function store()
    {
        try {
            $this->erroresFormulario = false;

            $this->validate([
                'nombre'           => 'required|string|max:255',
                'descripcion'      => 'nullable|string|max:500',
                'subcategoria_id'  => 'required|exists:subcategorias,id',
                'precio'           => 'required|numeric|min:0',
                'costo'            => 'nullable|numeric|min:0',
                'activo'           => 'required|boolean',
                'impuesto_id'      => 'nullable|exists:impuestos,id',
                'stockMinimoGlobal'=> 'nullable|integer|min:0',
                'stockMaximoGlobal'=> 'nullable|integer|min:0|gte:stockMinimoGlobal',
            ]);

            if (Producto::where('nombre', $this->nombre)->exists()) {
                $this->addError('nombre', 'Ya existe un producto registrado con este nombre.');
                $this->erroresFormulario = true;
                return;
            }

            $this->aplicarStockGlobalSiExiste();

            $producto = Producto::create([
                'nombre'          => $this->nombre,
                'descripcion'     => $this->descripcion,
                'precio'          => $this->precio,
                'costo'           => $this->costo ?? 0,
                'stock'           => 0,
                'stock_minimo'    => 0,
                'stock_maximo'    => null,
                'activo'          => $this->activo,
                'subcategoria_id' => $this->subcategoria_id,
                'impuesto_id'     => $this->impuesto_id, // 👈 IVA
            ]);

            foreach ($this->stocksPorBodega as $bodegaId => $stockData) {
                $producto->bodegas()->attach($bodegaId, [
                    'stock'        => 0,
                    'stock_minimo' => $stockData['stock_minimo'] ?? 0,
                    'stock_maximo' => $stockData['stock_maximo'] ?? null,
                ]);
            }

            $this->resetInput();
            PendingToast::create()->success()->message('Producto creado exitosamente.')->duration(5000);

        } catch (\Throwable $e) {
            Log::error('Error al guardar producto', ['message' => $e->getMessage()]);
            PendingToast::create()->error()->message('Error al guardar el producto: '.$e->getMessage())->duration(9000);
        }
    }

    /** ====== Actualizar ====== */
    public function update()
    {
        try {
            $this->validate([
                'nombre'          => 'required|string|max:255',
                'subcategoria_id' => 'required|exists:subcategorias,id',
                'precio'          => 'required|numeric|min:0',
                'costo'           => 'nullable|numeric|min:0',
                'impuesto_id'     => 'nullable|exists:impuestos,id',
            ]);

            $this->aplicarStockGlobalSiExiste();

            $producto = Producto::findOrFail($this->producto_id);

            $producto->update([
                'nombre'          => $this->nombre,
                'descripcion'     => $this->descripcion,
                'precio'          => $this->precio,
                'costo'           => $this->costo ?? 0,
                'activo'          => $this->activo,
                'subcategoria_id' => $this->subcategoria_id,
                'impuesto_id'     => $this->impuesto_id, // 👈 IVA
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

    /** ====== Cargar para editar ====== */
    public function edit($id)
    {
        try {
            $producto = Producto::with('bodegas')->findOrFail($id);

            $this->producto_id     = $producto->id;
            $this->nombre          = $producto->nombre;
            $this->descripcion     = $producto->descripcion;
            $this->precio          = $producto->precio;
            $this->costo           = $producto->costo;
            $this->activo          = (bool) $producto->activo;
            $this->subcategoria_id = $producto->subcategoria_id;
            $this->impuesto_id     = $producto->impuesto_id; // 👈 IVA
            $this->isEdit          = true;

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

    /** ====== Validación por campo ====== */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'nombre'           => 'required|string|max:255',
            'descripcion'      => 'nullable|string|max:500',
            'subcategoria_id'  => 'required|exists:subcategorias,id',
            'precio'           => 'required|numeric|min:0',
            'costo'            => 'nullable|numeric|min:0',
            'activo'           => 'required|boolean',
            'impuesto_id'      => 'nullable|exists:impuestos,id',
            'stockMinimoGlobal'=> 'nullable|integer|min:0',
            'stockMaximoGlobal'=> 'nullable|integer|min:0|gte:stockMinimoGlobal',
        ]);
    }

    /** ====== Helpers ====== */
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
            'nombre','descripcion','precio','costo','activo','subcategoria_id','impuesto_id',
            'producto_id','isEdit','bodegaSeleccionada','stockMinimo','stockMaximo',
            'stocksPorBodega','stockMinimoGlobal','stockMaximoGlobal'
        ]);
    }
      public function getImpuestoSeleccionadoProperty()
    {
        if (!$this->impuesto_id || !$this->impuestos) return null;
        return $this->impuestos->firstWhere('id', (int) $this->impuesto_id);
    }

    /** Computado: precio con IVA en tiempo real (sin guardar) */
    public function getPrecioConIvaTmpProperty(): float
    {
        $base = (float) ($this->precio ?? 0);
        $imp  = $this->impuestoSeleccionado; // usa el getter de arriba

        if (!$imp) return round($base, 2);

        if (!is_null($imp->porcentaje)) {
            return round($base * (1 + ((float)$imp->porcentaje / 100)), 2);
        }

        if (!is_null($imp->monto_fijo)) {
            return round($base + (float)$imp->monto_fijo, 2);
        }

        return round($base, 2);
    }
}
