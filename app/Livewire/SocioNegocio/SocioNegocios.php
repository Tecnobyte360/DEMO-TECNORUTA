<?php

namespace App\Livewire\SocioNegocio;

use App\Models\SocioNegocio\SocioNegocio;
use Livewire\Component;
use Livewire\WithFileUploads;

class SocioNegocios extends Component
{
    use WithFileUploads;


    
    public $socioNegocios = [];
    public $importFile;
    public $isLoading = false;  // Esta propiedad manejará el estado de carga
    public $editingSocioId = null;
    public function mount()
    {
        $this->loadSocios();
    }

    public function loadSocios()
    {
        $this->socioNegocios = SocioNegocio::all();
    }

    public function editsocio($id){
        $this->dispatch('loadEditSocio', $id);
    
    
    }
    

    public function import()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt',
        ]);

        $this->isLoading = true; // Inicia el estado de carga

        // Aquí iría la lógica de importación real, por ejemplo con Laravel Excel
        // Simulación de importación:
        sleep(2);  // Elimina esta línea después de la importación real

        $this->isLoading = false; // Termina el estado de carga

        session()->flash('message', 'Clientes importados correctamente.');
        $this->loadSocios();
        $this->importFile = null;
    }

    public function cancelar()
    {
        $this->reset(['importFile']);
        $this->resetValidation();
        session()->forget(['message', 'error', 'errores_importacion']);
    
        $this->dispatchBrowserEvent('close-import-modal'); // Cerrar el modal al cancelar
    }

    public function render()
    {
        return view('livewire.socio-negocio.socio-negocios');
    }
}
