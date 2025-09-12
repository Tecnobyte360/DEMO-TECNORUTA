<?php

namespace App\Livewire\Facturas;

use App\Models\Factura\factura;
use Livewire\Component;
use Livewire\Attributes\On;

use Masmerise\Toaster\PendingToast;

class PagosFactura extends Component
{
    public ?int $facturaId = null;

    public bool $show = false;
    public string $fecha = '';
    public ?string $metodo = null;
    public ?string $referencia = null;
    public float $monto = 0.0;
    public ?string $notas = null;

    protected $rules = [
        'fecha'      => 'required|date',
        'monto'      => 'required|numeric|min:0.01',
        'metodo'     => 'nullable|string|max:40',
        'referencia' => 'nullable|string|max:120',
        'notas'      => 'nullable|string',
    ];

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
    }

    public function render()
    {
        $factura = $this->facturaId ? factura::with('pagos')->find($this->facturaId) : null;
        return view('livewire.facturas.pagos-factura', compact('factura'));
    }

    #[On('abrir-modal-pago')]
    public function abrir(int $facturaId): void
    {
        $this->facturaId = $facturaId;
        $this->show = true;
        $this->fecha = now()->toDateString();
        $this->metodo = null;
        $this->referencia = null;
        $this->monto = 0.0;
        $this->notas = null;
        $this->resetErrorBag(); $this->resetValidation();
    }

    public function cerrar(): void
    {
        $this->show = false;
    }

    public function guardarPago(): void
    {
        $this->validate();
        if (!$this->facturaId) return;

        $fac = Factura::findOrFail($this->facturaId);
        $fac->registrarPago([
            'fecha'      => $this->fecha,
            'metodo'     => $this->metodo,
            'referencia' => $this->referencia,
            'monto'      => $this->monto,
            'notas'      => $this->notas,
        ]);

        PendingToast::create()->success()->message('Pago registrado.')->duration(4000);
        $this->dispatch('abrir-factura', id: $fac->id)->to(\App\Livewire\Facturas\FacturaForm::class);
        $this->show = false;
    }

    public function eliminarPago(int $pagoId): void
    {
        $fac = Factura::with('pagos')->findOrFail($this->facturaId);
        $fac->pagos()->whereKey($pagoId)->delete();
        $fac->recalcularTotales()->save();
        $this->dispatch('$refresh');
    }
}
