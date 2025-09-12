<?php

namespace App\Livewire\SocioNegocio;

use App\Models\SocioNegocio\SocioNegocio;
use App\Models\Pedidos\Pedido;
use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class SocioNegocios extends Component
{
    use WithFileUploads;

    /** Catálogo para TomSelect */
    public $clientesFiltrados = [];

    /** Listas separadas */
    public $clientes = [];
    public $proveedores = [];

    /** Import */
    public $importFile;
    public $isLoading = false;

    /** Filtros compartidos */
    public $socioNegocioId = null;   // selector puntual (TomSelect)
    public $buscador = '';           // texto libre (razón social, NIT, dirección)

    /** Modales / pedidos */
    public $socioPedidosId = null;
    public $pedidosSocio = [];
    public $mostrarDetalleModal = false;
    public $pedidoSeleccionado = null;
    public $detallesPedido = [];

    public function mount()
    {
        $this->loadClientesSelect();
        $this->loadListas();
    }

    /** === Catálogo TomSelect === */
    public function loadClientesSelect()
    {
        $this->clientesFiltrados = SocioNegocio::select('id', 'razon_social', 'nit')
            ->orderBy('razon_social')
            ->get()
            ->toArray();
    }

    /** === Carga dos columnas (clientes y proveedores) con filtros compartidos === */
   public function loadListas()
{
    $withRels = [
        'pedidos.pagos',
        'pedidos.detalles.producto',
        'pedidos.usuario',
        'pedidos.ruta',
    ];

    // Filtros comunes
    $base = SocioNegocio::with($withRels)
        ->when($this->socioNegocioId, fn($q) => $q->where('id', $this->socioNegocioId))
        ->when(trim($this->buscador) !== '', function ($q) {
            $t = '%'.trim($this->buscador).'%';
            $q->where(function ($qq) use ($t) {
                $qq->where('razon_social', 'like', $t)
                   ->orWhere('nit', 'like', $t)
                   ->orWhere('direccion', 'like', $t);
            });
        })
        ->orderBy('razon_social')
        ->get();

    // Calcular saldo pendiente y créditos
    $enriquecer = function ($s) {
        $creditos = $s->pedidos
            ->where('tipo_pago', 'credito')
            ->whereNull('cancelado')
            ->filter(function ($p) {
                $total  = $p->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $p->pagos->sum('monto');
                return $total > $pagado;
            })
            ->map(function ($p) {
                $total  = $p->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $p->pagos->sum('monto');
                return ['id' => $p->id, 'total_raw' => max(0, $total - $pagado)];
            })
            ->values();

        $s->creditosPendientes = $creditos;
        $s->saldoPendiente     = $creditos->sum('total_raw');
        return $s;
    };

    $base = $base->map($enriquecer);

    // Separar clientes y proveedores según 'C' y 'P'
    $this->clientes = $base->filter(function ($s) {
        $tipo = strtoupper(trim($s->tipo ?? ''));
        return $tipo === 'C';
    })->values();

    $this->proveedores = $base->filter(function ($s) {
        $tipo = strtoupper(trim($s->tipo ?? ''));
        return $tipo === 'P';
    })->values();
}


    /** Calcula saldo pendiente de crédito (no cancelado) y agrega props de apoyo */
    private function hydrateSaldo($socio)
    {
        $creditos = $socio->pedidos
            ->where('tipo_pago', 'credito')
            ->whereNull('cancelado')
            ->filter(function ($p) {
                $total  = $p->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $p->pagos->sum('monto');
                return $total > $pagado;
            })
            ->map(function ($p) {
                $total  = $p->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $p->pagos->sum('monto');
                return ['id' => $p->id, 'total_raw' => max(0, $total - $pagado)];
            })
            ->values();

        $socio->creditosPendientes = $creditos;
        $socio->saldoPendiente     = $creditos->sum('total_raw');

        return $socio;
    }

    /** Re-carga al cambiar filtros */
    public function updatedSocioNegocioId() { $this->loadListas(); }
    public function updatedBuscador()       { $this->loadListas(); }

    /** Acciones */
    public function editsocio($id)
    {
        $this->dispatch('loadEditSocio', $id);
    }

    public function import()
    {
        $this->validate(['importFile' => 'required|file|mimes:csv,txt']);
        $this->isLoading = true;
        // ... tu import real
        sleep(1);
        $this->isLoading = false;

        session()->flash('message', 'Socios importados correctamente.');
        $this->loadClientesSelect();
        $this->loadListas();
        $this->reset('importFile');
    }

    public function cancelar()
    {
        $this->reset(['importFile']);
        $this->resetValidation();
        session()->forget(['message','error','errores_importacion']);
        $this->dispatchBrowserEvent('close-import-modal');
    }

    /** Modal: pedidos del socio */
    public function mostrarPedidos($socioId)
    {
        $this->socioPedidosId = $socioId;

        $socio = SocioNegocio::with([
            'pedidos.pagos',
            'pedidos.usuario',
            'pedidos.ruta',
            'pedidos.detalles.producto'
        ])->find($socioId);

        if (!$socio) { $this->pedidosSocio = []; return; }

        $this->pedidosSocio = $socio->pedidos
            ->where('tipo_pago', 'credito')
            ->whereNull('cancelado')
            ->filter(function ($p) {
                $total  = $p->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $p->pagos->sum('monto');
                return $total > $pagado;
            })
            ->map(function ($p) {
                $total    = $p->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado   = $p->pagos->sum('monto');
                $pend     = max(0, $total - $pagado);
                return [
                    'id'            => $p->id,
                    'numero_pedido' => $p->numero_pedido ?: 'N/A',
                    'fecha'         => $p->fecha ? Carbon::parse($p->fecha)->format('d/m/Y') : '-',
                    'ruta'          => $p->ruta->ruta ?? 'Sin ruta',
                    'usuario'       => $p->usuario->name ?? 'Desconocido',
                    'total'         => number_format($pend, 2, ',', '.'),
                    'total_raw'     => $pend,
                    'tipo_pago'     => $p->tipo_pago ?? 'N/A',
                ];
            })
            ->values()
            ->toArray();

        $this->dispatch('abrir-modal-pedidos');
    }

    public function cerrarPedidosModal()
    {
        $this->socioPedidosId = null;
        $this->pedidosSocio = [];
        $this->mostrarDetalleModal = false;
        $this->pedidoSeleccionado = null;
        $this->detallesPedido = [];
    }

    /** Detalle dentro del modal */
    public function mostrarDetallePedido($pedidoId)
    {
        if ($this->pedidoSeleccionado && $this->pedidoSeleccionado->id === $pedidoId) return;

        $this->pedidoSeleccionado = Pedido::with(['detalles.producto'])->find($pedidoId);

        if ($this->pedidoSeleccionado) {
            $this->detallesPedido = $this->pedidoSeleccionado->detalles->map(function ($d) {
                $unit = floatval($d->precio_aplicado ?? $d->precio_unitario ?? 0);
                return [
                    'producto'        => $d->producto->nombre ?? 'Producto desconocido',
                    'cantidad'        => $d->cantidad,
                    'precio_unitario' => number_format($unit, 2, ',', '.'),
                    'subtotal'        => number_format($unit * $d->cantidad, 2, ',', '.'),
                ];
            })->toArray();

            $this->mostrarDetalleModal = true;
        }
    }

    public function render()
    {
        return view('livewire.socio-negocio.socio-negocios');
    }
}
