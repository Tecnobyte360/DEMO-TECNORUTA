<?php

namespace App\Livewire\Facturas;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

use App\Models\Serie\Serie;
use App\Models\Factura\factura as Factura;
use App\Models\SocioNegocio\SocioNegocio;
use App\Models\Productos\Producto;
use App\Models\bodegas;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Masmerise\Toaster\PendingToast;

class FacturaForm extends Component
{
    public ?Factura $factura = null;
    public string $documento = 'factura';
    public ?Serie $serieDefault = null;

    public ?int $serie_id = null;
    public ?int $socio_negocio_id = null;
    public string $fecha = '';
    public ?string $vencimiento = null;
    public string $tipo_pago = 'contado';
    public ?int $plazo_dias = null;
    public ?string $terminos_pago = null;
    public ?string $notas = null;
    public string $moneda = 'COP';

    public string $estado = 'borrador';
    public array $lineas = [];

    protected $rules = [
        'socio_negocio_id'         => 'required|exists:socio_negocios,id',
        'fecha'                    => 'required|date',
        'vencimiento'              => 'nullable|date|after_or_equal:fecha',
        'tipo_pago'                => 'required|in:contado,credito',
        'plazo_dias'               => 'nullable|integer|min:1|max:365',
        'lineas'                   => 'array|min:1',
        'lineas.*.producto_id'     => 'nullable|exists:productos,id',
        'lineas.*.descripcion'     => 'nullable|string|max:255',
        'lineas.*.cantidad'        => 'required|numeric|min:0.001',
        'lineas.*.precio_unitario' => 'required|numeric|min:0',
        'lineas.*.descuento_pct'   => 'nullable|numeric|min:0',
        'lineas.*.impuesto_pct'    => 'nullable|numeric|min:0',
    ];

    #[On('abrir-factura')]
    public function abrir(int $id): void
    {
        $this->cargarFactura($id);
    }

    public function mount(?int $id = null): void
    {
        try {
            $this->fecha = now()->toDateString();
            $this->serieDefault = Serie::defaultPara($this->documento);

            if ($id) {
                $this->cargarFactura($id);
                if (!$this->factura->serie_id && $this->serieDefault) {
                    $this->serie_id = $this->serieDefault->id;
                }
            } else {
                $this->addLinea();
                $this->aplicarFormaPago('contado');
                $this->serie_id = $this->serieDefault?->id;
            }
        } catch (Throwable $e) {
            report($e);
            PendingToast::create()->error()->message('No se pudo inicializar el formulario de factura.')->duration(7000);
        }
    }

    public function render()
    {
        try {
            $clientes = SocioNegocio::clientes()->orderBy('razon_social')->take(200)->get();
            $productos = Producto::with('impuesto:id,nombre,porcentaje,monto_fijo,incluido_en_precio,aplica_sobre,activo,vigente_desde,vigente_hasta')
                ->where('activo', 1)->orderBy('nombre')->take(300)->get();
            $bodegas = bodegas::orderBy('nombre')->get();

            return view('livewire.facturas.factura-form', [
                'clientes'     => $clientes,
                'productos'    => $productos,
                'bodegas'      => $bodegas,
                'series'       => $this->serieDefault ? collect([$this->serieDefault]) : collect(),
                'serieDefault' => $this->serieDefault,
            ]);
        } catch (Throwable $e) {
            report($e);
            PendingToast::create()->error()->message('No se pudo cargar datos auxiliares.')->duration(6000);
            return view('livewire.facturas.factura-form', [
                'clientes'     => collect(),
                'productos'    => collect(),
                'bodegas'      => collect(),
                'series'       => collect(),
                'serieDefault' => $this->serieDefault,
            ]);
        }
    }

    /* ================== REACTIVIDAD EN TIEMPO REAL ================== */

    /** Normaliza una línea (tipos, límites y redondeos) */
    private function normalizeLinea(array &$l): void
    {
        $l['cantidad']        = max(0.001, round((float)($l['cantidad'] ?? 0), 3));
        $l['precio_unitario'] = max(0.0,   round((float)($l['precio_unitario'] ?? 0), 2));
        $l['descuento_pct']   = max(0.0,   round((float)($l['descuento_pct'] ?? 0), 3));
        $l['impuesto_pct']    = max(0.0,   round((float)($l['impuesto_pct'] ?? 0), 3));
    }

    /** Hook general: cuando cambia producto/cantidad/precio/desc/iva, normaliza y refresca */
    public function updated($name, $value): void
    {
        // Producto seleccionado → precargar IVA/precio
        if (preg_match('/^lineas\.(\d+)\.producto_id$/', $name, $m)) {
            $i = (int) $m[1];
            $this->setProducto($i, $value);
            $this->resetErrorBag();
            $this->resetValidation();
            $this->dispatch('$refresh');
            return;
        }

        // Campos que afectan cálculo por línea
        if (preg_match('/^lineas\.(\d+)\.(cantidad|precio_unitario|descuento_pct|impuesto_pct)$/', $name, $m)) {
            $i = (int) $m[1];
            if (isset($this->lineas[$i])) {
                $this->normalizeLinea($this->lineas[$i]);
                $this->dispatch('$refresh'); // rerender inmediato
            }
            return;
        }

        // Cambios de fecha/plazo recalculan vencimiento
        if ($name === 'fecha') {
            $this->aplicarFormaPago($this->tipo_pago);
        }
        if ($name === 'plazo_dias' && $this->tipo_pago === 'credito') {
            $d = max((int)$this->plazo_dias, 1);
            $this->plazo_dias  = $d;
            $this->vencimiento = Carbon::parse($this->fecha)->addDays($d)->toDateString();
        }
    }

    /* ================== CARGA / LÍNEAS ================== */

    private function cargarFactura(int $id): void
    {
        try {
            $f = Factura::with(['detalles'])->findOrFail($id);
            $this->factura = $f;

            $this->fill($f->only([
                'serie_id','socio_negocio_id','fecha','vencimiento','tipo_pago',
                'plazo_dias','terminos_pago','notas','moneda','estado'
            ]));

            $this->lineas = $f->detalles->map(function ($d) {
                $l = [
                    'id'              => $d->id,
                    'producto_id'     => $d->producto_id,
                    'bodega_id'       => $d->bodega_id,
                    'descripcion'     => $d->descripcion,
                    'cantidad'        => (float)$d->cantidad,
                    'precio_unitario' => (float)$d->precio_unitario,
                    'descuento_pct'   => (float)$d->descuento_pct,
                    'impuesto_pct'    => (float)$d->impuesto_pct,
                ];
                $this->normalizeLinea($l);
                return $l;
            })->toArray();

            $this->resetErrorBag();
            $this->resetValidation();
        } catch (Throwable $e) {
            report($e);
            PendingToast::create()->error()->message('No se pudo cargar la factura.')->duration(7000);
        }
    }

    public function addLinea(): void
    {
        $l = [
            'producto_id'     => null,
            'bodega_id'       => null,
            'descripcion'     => null,
            'cantidad'        => 1,
            'precio_unitario' => 0,
            'descuento_pct'   => 0,
            'impuesto_pct'    => 0,
        ];
        $this->normalizeLinea($l);
        $this->lineas[] = $l;
        $this->dispatch('$refresh');
    }

    public function removeLinea(int $i): void
    {
        if (!isset($this->lineas[$i])) return;
        array_splice($this->lineas, $i, 1);
        $this->dispatch('$refresh');
    }

    /**
     * Al elegir un producto: precarga descripción, precio e impuesto.
     * Respeta activo/vigencia y si el IVA viene incluido en el precio.
     */
    public function setProducto(int $i, $id): void
    {
        try {
            if (!isset($this->lineas[$i])) return;

            $this->lineas[$i]['producto_id'] = $id ?: null;
            if (!$id) {
                $this->lineas[$i]['precio_unitario'] = 0;
                $this->lineas[$i]['impuesto_pct']    = 0;
                $this->dispatch('$refresh');
                return;
            }

            $p = Producto::with('impuesto')->find($id);
            if (!$p) return;

            $precioBase = (float) ($p->precio ?? $p->precio_venta ?? 0);
            $ivaPct     = 0.0;

            $imp = $p->impuesto;
            if ($imp && (int)($imp->activo ?? 0) === 1) {
                $aplica = strtoupper((string)($imp->aplica_sobre ?? ''));
                $aplicaVentas = in_array($aplica, ['VENTAS','VENTA','AMBOS','TODOS'], true);

                $hoy   = now()->startOfDay();
                $desde = $imp->vigente_desde ? Carbon::parse($imp->vigente_desde) : null;
                $hasta = $imp->vigente_hasta ? Carbon::parse($imp->vigente_hasta) : null;
                $vigente = (!$desde || $hoy->gte($desde)) && (!$hasta || $hoy->lte($hasta));

                if ($aplicaVentas && $vigente && !is_null($imp->porcentaje)) {
                    $ivaPct = (float) $imp->porcentaje;

                    if (!empty($imp->incluido_en_precio) && $ivaPct > 0) {
                        $precioBase = round($precioBase / (1 + $ivaPct / 100), 2);
                    }
                }
            }

            if (empty($this->lineas[$i]['descripcion'])) {
                $this->lineas[$i]['descripcion'] = $p->nombre;
            }

            $this->lineas[$i]['precio_unitario'] = $precioBase;
            $this->lineas[$i]['impuesto_pct']    = $ivaPct;
            $this->normalizeLinea($this->lineas[$i]);
            $this->dispatch('$refresh');
        } catch (Throwable $e) {
            report($e);
            PendingToast::create()->error()->message('No se pudo establecer el producto.')->duration(5000);
        }
    }

    /* ================== PAGO / FECHAS ================== */

    public function aplicarFormaPago(string $tipo): void
    {
        $this->tipo_pago = $tipo;

        if ($tipo === 'contado') {
            $this->plazo_dias  = null;
            $this->vencimiento = $this->fecha;
        } else {
            if (!$this->plazo_dias) $this->plazo_dias = 30;
            $this->vencimiento = Carbon::parse($this->fecha)->addDays($this->plazo_dias)->toDateString();
        }
    }

    public function updatedFecha(): void
    {
        $this->aplicarFormaPago($this->tipo_pago);
    }

    public function updatedPlazoDias(): void
    {
        if ($this->tipo_pago === 'credito') {
            $d = max((int)$this->plazo_dias, 1);
            $this->plazo_dias  = $d;
            $this->vencimiento = Carbon::parse($this->fecha)->addDays($d)->toDateString();
        }
    }

    /* ================== TOTALES (PROPIEDADES COMPUTADAS) ================== */

    public function getSubtotalProperty(): float
    {
        $s = 0.0;
        foreach ($this->lineas as $l) {
            $cant  = max(0, (float)($l['cantidad'] ?? 0));
            $precio= max(0, (float)($l['precio_unitario'] ?? 0));
            $desc  = max(0, (float)($l['descuento_pct'] ?? 0));
            $base  = $cant * $precio * (1 - $desc/100);
            $s    += $base;
        }
        return round($s, 2);
    }

    public function getImpuestosTotalProperty(): float
    {
        $i = 0.0;
        foreach ($this->lineas as $l) {
            $cant  = max(0, (float)($l['cantidad'] ?? 0));
            $precio= max(0, (float)($l['precio_unitario'] ?? 0));
            $desc  = max(0, (float)($l['descuento_pct'] ?? 0));
            $iva   = max(0, (float)($l['impuesto_pct'] ?? 0));
            $base  = $cant * $precio * (1 - $desc/100);
            $i    += $base * $iva/100;
        }
        return round($i, 2);
    }

    public function getTotalProperty(): float
    {
        return round($this->subtotal + $this->impuestosTotal, 2);
    }

    /* ================== PERSISTENCIA ================== */

    protected function persistirBorrador(): void
    {
        DB::transaction(function () {
            if (!$this->factura) {
                $this->factura = new Factura();
            }

            $this->factura->fill([
                'serie_id'         => $this->factura->serie_id ?? ($this->serieDefault?->id ?? $this->serie_id),
                'socio_negocio_id' => $this->socio_negocio_id,
                'fecha'            => $this->fecha,
                'vencimiento'      => $this->vencimiento,
                'moneda'           => $this->moneda,
                'tipo_pago'        => $this->tipo_pago,
                'plazo_dias'       => $this->plazo_dias,
                'terminos_pago'    => $this->terminos_pago,
                'notas'            => $this->notas,
                'estado'           => 'borrador',
            ])->save();

            $this->factura->detalles()->delete();

            foreach ($this->lineas as $l) {
                $this->factura->detalles()->create([
                    'producto_id'     => $l['producto_id'] ?? null,
                    'bodega_id'       => $l['bodega_id'] ?? null,
                    'descripcion'     => $l['descripcion'] ?? null,
                    'cantidad'        => (float)($l['cantidad'] ?? 0),
                    'precio_unitario' => (float)($l['precio_unitario'] ?? 0),
                    'descuento_pct'   => (float)($l['descuento_pct'] ?? 0),
                    'impuesto_pct'    => (float)($l['impuesto_pct'] ?? 0),
                ]);
            }

            $this->factura->load('detalles');
            $this->factura->recalcularTotales()->save();
            $this->estado = $this->factura->estado;
        }, 3);
    }

    public function guardar(): void
    {
        try {
            $this->validate();
            if (empty($this->lineas)) {
                PendingToast::create()->error()->message('Agrega al menos una línea.')->duration(4500);
                return;
            }
            $this->persistirBorrador();
            PendingToast::create()->success()->message('Factura guardada en borrador.')->duration(4500);
            $this->dispatch('refrescar-lista-facturas');
        } catch (ValidationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            $msg = config('app.debug') ? ($e->errorInfo[2] ?? $e->getMessage()) : 'Error de base de datos.';
            PendingToast::create()->error()->message($msg)->duration(9000);
        } catch (Throwable $e) {
            report($e);
            $msg = config('app.debug') ? $e->getMessage() : 'No se pudo guardar.';
            PendingToast::create()->error()->message($msg)->duration(9000);
        }
    }

    public function emitir(): void
    {
        try {
            $this->validate();
            if (empty($this->lineas)) {
                PendingToast::create()->error()->message('Agrega al menos una línea.')->duration(4500);
                return;
            }
            DB::transaction(function () {
                $this->persistirBorrador();

                if (!$this->serieDefault) {
                    throw new \RuntimeException('No hay serie default activa para este documento.');
                }

                $numero = $this->serieDefault->tomarConsecutivo();

                $this->factura->update([
                    'serie_id' => $this->serieDefault->id,
                    'numero'   => $numero,
                    'prefijo'  => $this->serieDefault->prefijo,
                    'estado'   => 'emitida',
                ]);

                $this->factura->recalcularTotales()->save();
                $this->estado = $this->factura->estado;
            }, 3);

            PendingToast::create()->success()->message('Factura emitida.')->duration(4500);
            $this->dispatch('refrescar-lista-facturas');
        } catch (ValidationException $e) {
            throw $e;
        } catch (QueryException $e) {
            report($e);
            $msg = config('app.debug') ? ($e->errorInfo[2] ?? $e->getMessage()) : 'Error de base de datos al emitir.';
            PendingToast::create()->error()->message($msg)->duration(9000);
        } catch (Throwable $e) {
            report($e);
            $msg = config('app.debug') ? $e->getMessage() : 'No se pudo emitir la factura.';
            PendingToast::create()->error()->message($msg)->duration(9000);
        }
    }

    public function anular(): void
    {
        try {
            if (!$this->factura?->id) return;
            $this->factura->update(['estado' => 'anulada']);
            $this->estado = 'anulada';
            PendingToast::create()->info()->message('Factura anulada.')->duration(4500);
            $this->dispatch('refrescar-lista-facturas');
        } catch (Throwable $e) {
            report($e);
            PendingToast::create()->error()->message('No se pudo anular.')->duration(7000);
        }
    }

    public function abrirPagos(): void
    {
        PendingToast::create()->info()->message('Abrir pagos (pendiente de implementar).')->duration(3500);
    }

    public function getProximoPreviewProperty(): ?string
    {
        try {
            $s = $this->factura?->serie_id
                ? Serie::find($this->factura->serie_id)
                : $this->serieDefault;

            if (!$s) return null;

            $n   = max((int)$s->proximo, (int)$s->desde);
            $len = $s->longitud ?? 6;
            $num = str_pad((string)$n, $len, '0', STR_PAD_LEFT);

            return ($s->prefijo ? "{$s->prefijo}-" : '') . $num;
        } catch (Throwable $e) {
            report($e);
            return null;
        }
    }
}
