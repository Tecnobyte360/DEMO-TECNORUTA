<?php

namespace App\Livewire\Bodegas;

use App\Models\bodegas;
use Livewire\Component;

class Bodega extends Component
{
    public $nombre, $ubicacion, $activo, $bodega_id;
    public $bodegas;
    public $showCreateModal = false;
    public $showEditModal = false;
    public $bodegaId; // Definir la propiedad bodegaId para ser usada en la vista

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'ubicacion' => 'required|string|max:255',
        'activo' => 'required|boolean',
    ];

    public function loadRoles()
    {
        // Puedes agregar lógica aquí para cargar roles o datos adicionales
        // Ejemplo: $this->roles = Role::all();
    }

    public function mount()
    {
        $this->loadRoles();
        $this->bodegas = bodegas::all() ?? collect();
    }

    public function listarBodegas()
    {
        $this->bodegas = bodegas::all();
    }

    public function guardar()
    {
        $this->validate();

        bodegas::updateOrCreate(
            ['id' => $this->bodega_id],
            ['nombre' => $this->nombre, 'ubicacion' => $this->ubicacion, 'activo' => $this->activo]
        );

        $this->resetInput();
        $this->listarBodegas();
        $this->showCreateModal = false; // Cerrar el modal después de guardar
    }

    public function editar($id)
    {
        $bodega = bodegas::find($id);
    
        if ($bodega) {
            $this->bodega_id = $bodega->id;
            $this->emit('cargarBodega', $bodega->id); // Enviar el ID al componente Edit
            $this->showEditModal = true;
        }
    }
    
    
    
    public function eliminar($id)
    {
        bodegas::destroy($id);
        $this->listarBodegas();
    }

    public function resetInput()
    {
        $this->nombre = '';
        $this->ubicacion = '';
        $this->activo = 1;
        $this->bodega_id = null;
        $this->bodegaId = null; // Resetear bodegaId
    }

    public function render()
    {
        return view('livewire.bodegas.bodega');
    }
}
