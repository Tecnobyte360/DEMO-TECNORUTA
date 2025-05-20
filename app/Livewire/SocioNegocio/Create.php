<?php

namespace App\Livewire\SocioNegocio;

use App\Models\SocioNegocio\SocioNegocio;
use Illuminate\Database\QueryException;
use Livewire\Component;
use Livewire\Attributes\On;

class Create extends Component
{
    public $razon_social, $nit, $telefono_fijo, $telefono_movil,
           $direccion, $correo, $municipio_barrio, $saldo_pendiente, $Tipo;

    protected $rules = [
        'razon_social'      => 'required|string|max:255',
        'nit'               => 'required|string|max:20',
        'direccion'         => 'required|string|max:255',
        'correo'            => 'nullable|email',
        'telefono_fijo'     => 'nullable|string|max:20',
        'telefono_movil'    => 'nullable|string|max:20',
        'municipio_barrio'  => 'nullable|string|max:100',
        'saldo_pendiente'   => 'nullable|numeric',
        'Tipo'              => 'required|in:C,P',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

  

    public function save()
    {
        $this->validate([
            'razon_social'     => 'required|string|max:255',
            'nit'              => 'required|string|max:20|unique:socio_negocios,nit',
            'telefono_fijo'    => 'nullable|digits_between:7,10',
            'telefono_movil'   => 'nullable|digits:10',
            'direccion'        => 'required|string|max:255',
            'correo'           => 'required|email|max:255',
            'municipio_barrio' => 'required|string|max:255',
            'saldo_pendiente'  => 'nullable|numeric|min:0',

        ], [
            'razon_social.required' => 'La razón social es obligatoria.',
            'nit.required'          => 'El NIT es obligatorio.',
            'nit.unique'            => 'Ya existe un socio de negocio con el NIT ingresado.',
            'telefono_fijo.digits_between' => 'El teléfono fijo debe tener entre 7 y 10 dígitos.',
            'telefono_movil.digits' => 'El teléfono móvil debe tener exactamente 10 dígitos.',
            'direccion.required'    => 'La dirección es obligatoria.',
            'correo.required'       => 'El correo es obligatorio.',
            'correo.email'          => 'El correo debe ser una dirección válida.',
            'municipio_barrio.required' => 'El municipio o barrio es obligatorio.',
            
        ]);
    
        try {
            SocioNegocio::create([
                'razon_social'     => $this->razon_social,
                'nit'              => $this->nit,
                'telefono_fijo'    => $this->telefono_fijo,
                'telefono_movil'   => $this->telefono_movil,
                'direccion'        => $this->direccion,
                'correo'           => $this->correo,
                'municipio_barrio' => $this->municipio_barrio,
                'saldo_pendiente'  => $this->saldo_pendiente,
                'Tipo'             => $this->Tipo,
            ]);
    
            session()->flash('message', 'Socio de negocio creado exitosamente!');
            $this->reset([
                'razon_social', 'nit', 'telefono_fijo', 'telefono_movil',
                'direccion', 'correo', 'municipio_barrio', 'saldo_pendiente', 'Tipo'
            ]);
            $this->emitUp('refreshList');
    
        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error inesperado: ' . $this->formatearError($e->getMessage()));
        }
    }
    
    
    /**
     * Devuelve un mensaje más limpio del error SQL.
     */
    protected function formatearError($mensaje)
    {
        // Puedes hacer más limpia esta función según lo que quieras mostrar
        // Aquí simplemente eliminamos detalles innecesarios
        return preg_replace('/SQLSTATE\[.*?\]: /', '', $mensaje);
    }
    
    
    

    public function render()
    {
        return view('livewire.socio-negocio.create');
    }
}