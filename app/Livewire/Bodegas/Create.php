<?php

namespace App\Livewire\Bodegas;

use Livewire\Component;
use App\Models\Bodegas;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Validation\Rule;

class Create extends Component
{
    public $nombre, $ubicacion, $activo = true, $bodega_id;
    public $mensaje = '';
    public $tipoMensaje = 'success';

   protected function rules()
{
    return [
        'nombre'       => ['required','string','max:120'],
        'prefijo'      => ['nullable','string','max:10',
            Rule::unique('series','prefijo')->where(fn($q)=>$q->where('nombre',$this->nombre))
                ->ignore($this->serie_id)
        ],
        'rango_desde'  => ['required','integer','min:1'],
        'rango_hasta'  => ['required','integer','gte:rango_desde'],
        'proximo_ui'   => ['required','integer','gte:rango_desde','lte:rango_hasta'], // 👈 NUEVA
        'longitud'     => ['required','integer','min:1','max:12'],
        'resolucion'   => ['nullable','string','max:120'],
        'fecha_inicio' => ['nullable','date'],
        'fecha_fin'    => ['nullable','date','after_or_equal:fecha_inicio'],
        'activo'       => ['boolean'],
    ];
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
