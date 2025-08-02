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

    public $socioNegocios = [];
    public $importFile;
    public $isLoading = false;
    public $editingSocioId = null;

    // Filtro por cliente
    public $socioNegocioId = null;
    public $clientesFiltrados = [];

    // Modal Pedidos
    public $socioPedidosId = null;
    public $pedidosSocio = [];

    // Modal Detalle Pedido
    public $mostrarDetalleModal = false;
    public $pedidoSeleccionado = null;
    public $detallesPedido = [];

    public function mount()
    {
        $this->loadClientes();
        $this->loadSocios();
    }

    public function loadClientes()
    {
        $this->clientesFiltrados = SocioNegocio::select('id', 'razon_social', 'nit')
            ->orderBy('razon_social')
            ->get()
            ->toArray();
    }

  public function loadSocios()
{
    $this->socioNegocios = SocioNegocio::with([
        'pedidos.pagos',
        'pedidos.detalles.producto',
        'pedidos.usuario',
        'pedidos.ruta'
    ])
    ->when($this->socioNegocioId, function ($query) {
        $query->where('id', $this->socioNegocioId);
    })
    ->get()
    ->map(function ($socio) {
        $pedidosSocio = $socio->pedidos
            ->where('tipo_pago', 'credito')
            ->whereNull('cancelado') // ⬅️ Aquí se filtran los pedidos cancelados
            ->filter(function ($pedido) {
                $total = $pedido->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $pedido->pagos->sum('monto');
                return $total > $pagado;
            })
            ->map(function ($pedido) {
                $total = $pedido->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $pedido->pagos->sum('monto');
                $pendiente = $total - $pagado;

                return [
                    'id'            => $pedido->id,
                    'total_raw'     => $pendiente,
                ];
            })
            ->values();

        $socio->creditosPendientes = $pedidosSocio;

        return $socio;
    })
    ->values();
}


    public function updatedSocioNegocioId()
    {
        $this->loadSocios();
    }

    public function editsocio($id)
    {
        $this->dispatch('loadEditSocio', $id);
    }

    public function import()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt',
        ]);

        $this->isLoading = true;
        sleep(2);
        $this->isLoading = false;

        session()->flash('message', 'Clientes importados correctamente.');
        $this->loadClientes();
        $this->loadSocios();
        $this->importFile = null;
    }

    public function cancelar()
    {
        $this->reset(['importFile']);
        $this->resetValidation();
        session()->forget(['message', 'error', 'errores_importacion']);
        $this->dispatchBrowserEvent('close-import-modal');
    }

    public function mostrarPedidos($socioId)
    {
        $this->socioPedidosId = $socioId;

        $socio = SocioNegocio::with([
            'pedidos.pagos',
            'pedidos.usuario',
            'pedidos.ruta',
            'pedidos.detalles.producto'
        ])->find($socioId);

        if (!$socio) {
            $this->pedidosSocio = [];
            return;
        }

        $this->pedidosSocio = $socio->pedidos
            ->where('tipo_pago', 'credito')
            ->filter(function ($pedido) {
                $total = $pedido->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $pedido->pagos->sum('monto');
                return $total > $pagado; // Filtra solo si tiene saldo pendiente
            })
            ->map(function ($pedido) {
                $total = $pedido->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $pedido->pagos->sum('monto');
                $pendiente = $total - $pagado;

                return [
                    'id'            => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido ?: 'N/A',
                    'fecha'         => $pedido->fecha ? Carbon::parse($pedido->fecha)->format('d/m/Y') : '-',
                    'ruta'          => $pedido->ruta->ruta ?? 'Sin ruta',
                    'usuario'       => $pedido->usuario->name ?? 'Desconocido',
                    'total'         => number_format($pendiente, 2, ',', '.'),
                    'total_raw'     => $pendiente,
                    'tipo_pago'     => $pedido->tipo_pago ?? 'N/A',
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
    }

    public function mostrarDetallePedido($pedidoId)
    {
        // Solo cambiar si es otro pedido
        if ($this->pedidoSeleccionado && $this->pedidoSeleccionado->id === $pedidoId) {
            return;
        }

        $this->pedidoSeleccionado = Pedido::with(['detalles.producto'])->find($pedidoId);

        if ($this->pedidoSeleccionado) {
            $this->detallesPedido = $this->pedidoSeleccionado->detalles->map(function ($detalle) {
                $unit = floatval($detalle->precio_aplicado ?? $detalle->precio_unitario ?? 0);
                return [
                    'producto'        => $detalle->producto->nombre ?? 'Producto desconocido',
                    'cantidad'        => $detalle->cantidad,
                    'precio_unitario' => number_format($unit, 2, ',', '.'),
                    'subtotal'        => number_format($unit * $detalle->cantidad, 2, ',', '.'),
                ];
            })->toArray();

            $this->mostrarDetalleModal = true;
        }
    }


    public function getSaldoPendienteCreditoAttribute()
    {
        return $this->pedidos
            ->where('tipo_pago', 'credito')
            ->filter(function ($pedido) {
                $total = $pedido->detalles->sum(function ($d) {
                    return $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario);
                });
                $pagado = $pedido->pagos->sum('monto');
                return $total > $pagado;
            })
            ->sum(function ($pedido) {
                $total = $pedido->detalles->sum(function ($d) {
                    return $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario);
                });
                $pagado = $pedido->pagos->sum('monto');
                return $total - $pagado;
            });
    }

    public function render()
    {
        return view('livewire.socio-negocio.socio-negocios');
    }
}
