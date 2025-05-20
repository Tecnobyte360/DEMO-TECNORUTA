<?php
// En el componente Create, cambiar la lógica para manejar tanto creación como edición
namespace App\Livewire\Bodegas;

use Livewire\Component;
use App\Models\Bodegas;
use Illuminate\Database\QueryException;
use Exception;

class Create extends Component
{
    public $nombre, $ubicacion, $activo = true, $bodega_id;
    public $mensaje = ''; // ✅ Propiedad para mensajes de éxito/error

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'ubicacion' => 'required|string|max:255',
        'activo' => 'boolean',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre de la bodega es obligatorio.',
        'ubicacion.required' => 'La ubicación es obligatoria.',
    ];

    public function mount($bodegaId = null)
    {
        if ($bodegaId) {
            $this->bodega_id = $bodegaId;
            $this->cargarBodega($bodegaId);
        }
    }

    public function cargarBodega($id)
    {
        $bodega = Bodegas::find($id);
        if ($bodega) {
            $this->nombre = $bodega->nombre;
            $this->ubicacion = $bodega->ubicacion;
            $this->activo = $bodega->activo;
        }
    }

    public function guardar()
    {
        $this->validate();
    
        try {
            if ($this->bodega_id) {
                // Si existe una bodega_id, actualizarla
                $bodega = Bodegas::findOrFail($this->bodega_id);
                $bodega->update([
                    'nombre' => $this->nombre,
                    'ubicacion' => $this->ubicacion,
                    'activo' => $this->activo,
                ]);
                $this->mensaje = '✅ Bodega actualizada exitosamente.';
            } else {
                // Si no existe bodega_id, crear una nueva
                Bodegas::create([
                    'nombre' => $this->nombre,
                    'ubicacion' => $this->ubicacion,
                    'activo' => $this->activo,
                ]);
                $this->mensaje = '✅ Bodega creada exitosamente.';
            }

            $this->reset(['nombre', 'ubicacion', 'activo']);
            $this->dispatch('cerrarModal');
            $this->dispatch('bodegaCreada');
        } catch (QueryException $e) {
            $this->mensaje = '❌ Error en la base de datos: ' . $e->getMessage();
        } catch (Exception $e) {
            $this->mensaje = '⚠ Ocurrió un error inesperado: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.bodegas.create');
    }
}
