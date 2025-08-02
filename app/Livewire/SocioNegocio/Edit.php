<?php

namespace App\Livewire\SocioNegocio;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\SocioNegocio\SocioNegocio;
use Masmerise\Toaster\PendingToast;

class Edit extends Component
{
    public $socioId;
    public $razon_social, $nit, $tipo, $telefono_fijo, $telefono_movil, $correo, $direccion, $municipio_barrio, $saldo_pendiente;
    public $pedidosSocio = [];

    #[On('loadEditSocio')]
    public function loadEditSocio($id)
    {
        $this->edit($id);
    }

    public function edit($id)
    {
        $this->socioId = $id;

        $socio = SocioNegocio::findOrFail($id);

        $this->razon_social = $socio->razon_social;
        $this->nit = $socio->nit;
        $this->tipo = $socio->tipo;
        $this->telefono_fijo = $socio->telefono_fijo;
        $this->telefono_movil = $socio->telefono_movil;
        $this->correo = $socio->correo;
        $this->direccion = $socio->direccion;
        $this->municipio_barrio = $socio->municipio_barrio;
        $this->saldo_pendiente = $socio->saldo_pendiente;
    }

    public function save()
    {
        $this->validate([
            'razon_social' => 'required',
            'nit' => 'required',
            'tipo' => 'required',
            'correo' => 'required|email',
            'direccion' => 'required',
        ]);

        try {
            $socio = SocioNegocio::with('pedidos')->findOrFail($this->socioId);

            if ($socio->pedidos()->exists() && $this->nit !== $socio->nit) {
                PendingToast::create()
                    ->error()
                    ->message('No puedes modificar el NIT de un socio con pedidos registrados.')
                    ->duration(7000);
                return;
            }

            $socio->update([
                'razon_social' => $this->razon_social,
                'nit' => $this->nit,
                'tipo' => $this->tipo,
                'telefono_fijo' => $this->telefono_fijo,
                'telefono_movil' => $this->telefono_movil,
                'correo' => $this->correo,
                'direccion' => $this->direccion,
                'municipio_barrio' => $this->municipio_barrio,
                'saldo_pendiente' => $this->saldo_pendiente,
            ]);

            PendingToast::create()
                ->success()
                ->message('Socio de negocio actualizado correctamente.')
                ->duration(5000);

            $this->dispatch('socioActualizado');
            $this->dispatch('cerrar-modal-edit');

        } catch (\Throwable $e) {
            PendingToast::create()
                ->error()
                ->message('Ocurrió un error: ' . $e->getMessage())
                ->duration(8000);
        }
    }

    public function render()
    {
        return view('livewire.socio-negocio.edit');
    }
}
