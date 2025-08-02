<?php

namespace App\Livewire\Finanzas;

use App\Models\Devoluciones\Devolucion;
use App\Models\Devoluciones\DevolucionDetalle;
use App\Models\Inventario\EntradaDetalle;
use App\Models\Pedidos\Pedido;
use App\Models\InventarioRuta\GastoRuta;
use App\Models\Pago;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Masmerise\Toaster\PendingToast;

class Liquidacion extends Component
{
    public $fechaInicio;
    public $fechaFin;

    protected $resumenConductores = [];
    public $gastosAdministrativos = [];
    public $todosLosGastos = [];

    public function mount()
    {
        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin    = Carbon::now()->format('Y-m-d');
        $this->generarResumenConductores();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['fechaInicio', 'fechaFin'])) {
            $this->generarResumenConductores();
        }
    }

    public function buscar()
    {
        $this->generarResumenConductores();
    }

    public function generarResumenConductores()
    {
        try {
            $resumen = [];
            $yaSumadoGastos = [];
            $yaProcesadoDevolucion = [];

            $pedidos = Pedido::with([
                'detalles.producto',
                'detalles.precioLista',
                'ruta.conductores',
                'pagos'
            ])
                ->where('estado', '!=', 'cancelado')
                ->whereBetween('fecha', [$this->fechaInicio, $this->fechaFin])
                ->get();

            foreach ($pedidos as $pedido) {
                $fecha = $pedido->fecha->format('Y-m-d');

                foreach ($pedido->ruta->conductores ?? [] as $conductor) {
                    $clave = "{$conductor->id}_{$fecha}";

                    if (!isset($resumen[$clave])) {
                        $resumen[$clave] = [
                            'nombre'                    => $conductor->name,
                            'fecha'                     => $fecha,
                            'detalles'                  => [],
                            'total_facturado'           => 0,
                            'total_gastos'              => 0,
                            'total_devoluciones'        => 0,
                            'total_pagos_contado'       => 0,
                            'total_pagos_credito'       => 0,
                            'total_pagos_transferencia' => 0,
                            'pagos_credito_anteriores'  => 0,
                            'total_liquidar'            => 0,
                            'gastos_detalle'            => [],
                            'utilidad'                  => 0,
                        ];
                    }

                    foreach ($pedido->detalles as $d) {
                        $costoUnitario = EntradaDetalle::where('producto_id', $d->producto_id)
                            ->orderByDesc('created_at')
                            ->value('precio_unitario') ?? 0;

                        $precioVenta = floatval($d->precio_unitario);
                        $cantidad = $d->cantidad;
                        $subtotal = $precioVenta * $cantidad;
                        $utilidad = ($precioVenta - $costoUnitario) * $cantidad;

                        $resumen[$clave]['detalles'][] = [
                            'tipo'         => 'venta',
                            'producto'     => $d->producto->nombre,
                            'cantidad'     => $cantidad,
                            'precio_base'  => $costoUnitario,
                            'precio_venta' => $precioVenta,
                            'lista'        => $d->precioLista->nombre ?? 'Precio Base',
                            'subtotal'     => $subtotal,
                            'utilidad'     => $utilidad,
                            'costo'        => $costoUnitario * $cantidad,
                        ];

                        $resumen[$clave]['total_facturado'] += $subtotal;
                        $resumen[$clave]['utilidad'] += $utilidad;
                    }

                    if ($pedido->detalles->isEmpty()) {
                        if ($pedido->tipo_pago === 'credito' && $pedido->valor_credito) {
                            $resumen[$clave]['total_facturado'] += floatval($pedido->valor_credito);
                        } elseif (in_array($pedido->tipo_pago, ['contado', 'transferencia']) && $pedido->valor_pagado) {
                            $resumen[$clave]['total_facturado'] += floatval($pedido->valor_pagado);
                        }
                    }

                    if (!isset($yaSumadoGastos[$clave])) {
                        $gRuta = GastoRuta::with('tipoGasto')
                            ->where('ruta_id', $pedido->ruta_id)
                            ->whereDate('created_at', $fecha)
                            ->get();

                        $resumen[$clave]['total_gastos'] = $gRuta->sum('monto');

                        $porTipo = $gRuta->groupBy(fn($x) => optional($x->tipoGasto)->nombre ?? 'Sin clasificar')
                            ->map(fn($grupo) => $grupo->sum('monto'));

                        foreach ($porTipo as $tipo => $monto) {
                            $resumen[$clave]['gastos_detalle'][$tipo] = $monto;
                        }

                        $yaSumadoGastos[$clave] = true;
                    }

                    if (!isset($yaProcesadoDevolucion[$clave])) {
                        $devs = DevolucionDetalle::with(['producto', 'devolucion'])
                            ->whereHas('devolucion', function ($q) use ($pedido, $conductor, $fecha) {
                                $q->where('ruta_id', $pedido->ruta_id)
                                    ->where('user_id', $conductor->id)
                                    ->whereDate('fecha', $fecha);
                            })->get();

                        foreach ($devs as $dev) {
                            $costoUnitario = EntradaDetalle::where('producto_id', $dev->producto_id)
                                ->orderByDesc('created_at')
                                ->value('precio_unitario') ?? 0;

                            $precioVenta = floatval($dev->precio_unitario ?? 0);
                            $cantidad = $dev->cantidad;
                            $subtotal = $precioVenta * $cantidad;
                            $utilidad = ($precioVenta - $costoUnitario) * $cantidad;

                            $resumen[$clave]['detalles'][] = [
                                'tipo'         => 'devolución',
                                'producto'     => $dev->producto->nombre ?? 'Producto',
                                'cantidad'     => -abs($cantidad),
                                'precio_base'  => $costoUnitario,
                                'precio_venta' => -abs($precioVenta),
                                'lista'        => '— Devolución',
                                'subtotal'     => -abs($subtotal),
                                'utilidad'     => -abs($utilidad),
                                'costo'        => -abs($costoUnitario * $cantidad),
                            ];

                            $resumen[$clave]['total_devoluciones'] += abs($subtotal);
                            $resumen[$clave]['utilidad'] -= abs($utilidad);
                        }

                        $yaProcesadoDevolucion[$clave] = true;
                    }

                    foreach ($pedido->pagos as $pago) {
                        match ($pago->metodo_pago) {
                            'credito'       => $resumen[$clave]['total_pagos_credito'] += $pago->monto,
                            'transferencia' => $resumen[$clave]['total_pagos_transferencia'] += $pago->monto,
                            default         => $resumen[$clave]['total_pagos_contado'] += $pago->monto,
                        };
                    }

                    if ($pedido->pagos->isEmpty()) {
                        if ($pedido->tipo_pago === 'credito' && $pedido->valor_credito) {
                            $resumen[$clave]['total_pagos_credito'] += floatval($pedido->valor_credito);
                        } elseif (in_array($pedido->tipo_pago, ['contado', 'transferencia']) && $pedido->valor_pagado) {
                            $resumen[$clave]['total_pagos_contado'] += floatval($pedido->valor_pagado);
                        }
                    }
                }
            }

    $pagosExtra = Pago::with('pedido.ruta.conductores')
    ->whereBetween('created_at', [$this->fechaInicio . ' 00:00:00', $this->fechaFin . ' 23:59:59'])
    ->where('metodo_pago', 'contado')
    ->get()
    ->filter(function ($pago) {
        return $pago->pedido
            && Carbon::parse($pago->pedido->fecha)->lt(Carbon::parse($pago->created_at)->startOfDay());
    });


foreach ($pagosExtra as $pago) {
    $fechaPago = Carbon::parse($pago->created_at)->format('Y-m-d');
$usuarioPago = $pago->usuario; // el user que registró el pago

if ($usuarioPago) {
    $clave = "{$usuarioPago->id}_{$fechaPago}";

    if (!isset($resumen[$clave])) {
        $resumen[$clave] = [
            'nombre'                    => $usuarioPago->name,
            'fecha'                     => $fechaPago,
            'detalles'                  => [],
            'total_facturado'           => 0,
            'total_gastos'              => 0,
            'total_devoluciones'        => 0,
            'total_pagos_contado'       => 0,
            'total_pagos_credito'       => 0,
            'total_pagos_transferencia' => 0,
            'pagos_credito_anteriores'  => 0,
            'pagos_credito_anteriores_detalle' => [],
            'total_liquidar'            => 0,
            'gastos_detalle'            => [],
            'utilidad'                  => 0,
        ];
    }

    $resumen[$clave]['pagos_credito_anteriores'] += $pago->monto;

    $resumen[$clave]['pagos_credito_anteriores_detalle'][] = [
        'pedido_id'          => $pago->pedido_id,
        'fecha_pedido'       => optional($pago->pedido)->fecha?->format('d/m/Y') ?? 'Sin fecha',
        'conductor_original' => optional($pago->pedido->ruta->conductores->first())->name ?? '—',
    ];
}

}



            foreach ($resumen as $clave => &$fila) {
                $fila['total_liquidar'] =
                    $fila['total_facturado']
                    - $fila['total_devoluciones']
                    - $fila['total_gastos']
                    - $fila['total_pagos_transferencia'];
                  
            }

            $this->todosLosGastos = GastoRuta::with('tipoGasto')
                ->whereBetween('created_at', [$this->fechaInicio . ' 00:00:00', $this->fechaFin . ' 23:59:59'])
                ->get();

            $this->gastosAdministrativos = $this->todosLosGastos->whereNull('ruta_id')->values();
            $this->resumenConductores = collect($resumen)->values();

            PendingToast::create()
                ->{$this->resumenConductores->isEmpty() ? 'info' : 'success'}()
                ->message($this->resumenConductores->isEmpty()
                    ? 'ℹ No hay registros.'
                    : 'Liquidación generada correctamente.')
                ->duration(5000);
        } catch (\Throwable $e) {
            Log::error('Error liquidación', ['e' => $e->getMessage()]);
            PendingToast::create()
                ->error()
                ->message('Error al calcular la liquidación.')
                ->duration(8000);
        }
    }

    public function render()
    {
        $r = collect($this->resumenConductores);
        $tf  = $r->sum('total_facturado');
        $tg  = $r->sum('total_gastos');
        $td  = $r->sum('total_devoluciones');
        $tc  = $r->sum('total_pagos_contado');
        $tcr = $r->sum('total_pagos_credito');
        $tt  = $r->sum('total_pagos_transferencia');
        $ta  = $r->sum('pagos_credito_anteriores');
        $na  = $tf - $tg - $td - $tt - $ta;

        return view('livewire.finanzas.liquidacion', [
            'resumenConductores' => $this->resumenConductores,
            'totalFacturado'             => $tf,
            'totalGastos'                => $tg,
            'totalDevoluciones'          => $td,
            'totalPagosContado'          => $tc,
            'totalPagosCredito'          => $tcr,
            'totalPagosTransferencia'    => $tt,
            'totalPagosAnteriores'       => $ta,
            'netoAcumulado'              => $na,
            'totalGastosAdministrativos' => collect($this->gastosAdministrativos)->sum('monto'),
        ]);
    }
}