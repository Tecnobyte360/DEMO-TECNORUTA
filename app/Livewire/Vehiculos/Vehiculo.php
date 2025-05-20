<?php

namespace App\Livewire\Vehiculos;

use Livewire\Component;
use App\Models\Vehiculo\Vehiculo as VehiculoModel;

class Vehiculo extends Component
{
    public $vehiculos;

    public $placa;
    public $modelo;
    public $marca;
    public $vehiculo_id;
    public $isEdit = false;

    public function mount()
    {
        $this->cargarVehiculos();
    }

    public function cargarVehiculos()
    {
        $this->vehiculos = VehiculoModel::latest()->get();
    }

    public function guardarVehiculo()
    {
        $this->validate([
            'placa' => 'required|unique:vehiculos,placa,' . $this->vehiculo_id,
            'modelo' => 'required|string',
            'marca' => 'nullable|string',
        ]);

        VehiculoModel::updateOrCreate(
            ['id' => $this->vehiculo_id],
            [
                'placa' => $this->placa,
                'modelo' => $this->modelo,
                'marca' => $this->marca,
            ]
        );

        $this->resetFormulario();
        $this->cargarVehiculos();
        $this->dispatch('vehiculo-guardado', ['mensaje' => $this->isEdit ? 'Vehículo actualizado.' : 'Vehículo registrado.']);
    }

    public function editar($id)
    {
        $vehiculo = VehiculoModel::findOrFail($id);
        $this->vehiculo_id = $vehiculo->id;
        $this->placa = $vehiculo->placa;
        $this->modelo = $vehiculo->modelo;
        $this->marca = $vehiculo->marca;
        $this->isEdit = true;
    }

    public function eliminar($id)
    {
        VehiculoModel::findOrFail($id)->delete();
        $this->cargarVehiculos();
        $this->dispatch('vehiculo-eliminado', ['mensaje' => 'Vehículo eliminado.']);
    }

    public function resetFormulario()
    {
        $this->reset(['vehiculo_id', 'placa', 'modelo', 'marca', 'isEdit']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.vehiculos.vehiculo');
    }
}
