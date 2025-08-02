<?php

namespace App\Livewire\Bodegas;

use Livewire\Component;
use App\Models\Bodegas;
use Illuminate\Database\QueryException;
use Exception;

class Create extends Component
{
    public $nombre, $ubicacion, $activo = true, $bodega_id;
    public $mensaje = '';
    public $tipoMensaje = 'success';

    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'ubicacion' => 'required|string|max:255',
            'activo' => 'boolean',
        ];

        // Validación única para nombre (evita duplicados)
        if (!$this->bodega_id) {
            $rules['nombre'] .= '|unique:bodegas,nombre';
        } else {
            $rules['nombre'] .= '|unique:bodegas,nombre,' . $this->bodega_id;
        }

        return $rules;
    }

    protected $messages = [
        'nombre.required' => 'El nombre de la bodega es obligatorio.',
        'nombre.unique' => 'Ya existe una bodega con ese nombre.',
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
                $bodega = Bodegas::findOrFail($this->bodega_id);
                $bodega->update([
                    'nombre' => $this->nombre,
                    'ubicacion' => $this->ubicacion,
                    'activo' => $this->activo,
                ]);
                $this->mensaje = '✅ Bodega actualizada exitosamente.';
                $this->tipoMensaje = 'success';
            } else {
                Bodegas::create([
                    'nombre' => $this->nombre,
                    'ubicacion' => $this->ubicacion,
                    'activo' => $this->activo,
                ]);
                $this->mensaje = '✅ Bodega creada exitosamente.';
                $this->tipoMensaje = 'success';
            }

            $this->reset(['nombre', 'ubicacion', 'activo']);
            $this->dispatch('cerrarModal');
            $this->dispatch('bodegaCreada');

        } catch (QueryException $e) {
            // Errores de base de datos, como violación de clave foránea, timeouts, etc.
            $this->mensaje = '❌ Error en la base de datos: ' . $e->getMessage();
            $this->tipoMensaje = 'error';
            // optional: \Log::error($e);
        } catch (Exception $e) {
            // Otros errores como variables nulas, errores de lógica, etc.
            $this->mensaje = '⚠ Ocurrió un error inesperado: ' . $e->getMessage();
            $this->tipoMensaje = 'warning';
            // optional: \Log::warning($e);
        }
    }

    public function render()
    {
        return view('livewire.bodegas.create');
    }
}
