<?php



namespace App\Livewire\RutaDisponiblesConductor;

use Livewire\Component;
use App\Models\Ruta\Ruta;
use App\Models\InventarioRuta\InventarioRuta;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RutasDisponiblesConductor extends Component
{
    public $rutas;
    public $rutaVistaId;
    public $inventarioVista = [];

    public function mount()
    {
        $usuarioId = Auth::id();

        $this->rutas = Ruta::whereDate('fecha_salida', Carbon::today())
            ->whereHas('conductores', function ($query) use ($usuarioId) {
                $query->where('users.id', $usuarioId);
            })
            ->with(['vehiculo', 'conductores'])
            ->get();
    }

    public function verInventario($rutaId)
    {
        $this->rutaVistaId = $rutaId;

        $this->inventarioVista = InventarioRuta::where('ruta_id', $rutaId)
            ->with(['producto', 'bodega'])
            ->get()
            ->map(function ($item) {
                return [
                    'producto' => $item->producto->nombre ?? '-',
                    'bodega'   => $item->bodega->nombre ?? '-',
                    'cantidad' => $item->cantidad,
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.ruta-disponibles-conductor.rutas-disponibles-conductor');
    }
}
