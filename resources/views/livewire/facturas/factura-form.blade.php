{{-- resources/views/livewire/facturas/factura-form.blade.php --}}
<div class="p-6">
  <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6">

    {{-- ============== ENCABEZADO ============== --}}
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
          <i class="fas fa-file-invoice-dollar text-purple-600"></i>
          Factura de Venta
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ $factura?->id ? 'Edita la factura y sus líneas.' : 'Crea una nueva factura y define sus líneas.' }}
        </p>
      </div>
      @if($factura?->id)
        <span class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
          Modo edición
        </span>
      @endif
    </div>

    {{-- ============== CABECERA DEL DOCUMENTO ============== --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
      {{-- Cliente --}}
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Cliente <span class="text-red-500">*</span>
        </label>
        <select wire:model.live="socio_negocio_id"
                class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                       shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500
                       @error('socio_negocio_id') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">
          <option value="">— Seleccione —</option>
          @foreach($clientes as $c)
            <option value="{{ $c->id }}">{{ $c->razon_social }} ({{ $c->nit }})</option>
          @endforeach
        </select>
        @error('socio_negocio_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
      </div>

      {{-- Serie (default + manual) --}}
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Serie (para emitir)</label>
        <div class="flex gap-2">
          <select wire:model.live="serie_id"
                  class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                         shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
            <option value="">— Seleccione —</option>
            @foreach($series as $s)
              <option value="{{ $s->id }}">{{ $s->nombre }} ({{ $s->prefijo }}: {{ $s->proximo }} → {{ $s->hasta }})</option>
            @endforeach
          </select>
          @if($serieDefault)
            <span class="inline-flex items-center text-xs px-3 rounded-xl bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200">
              {{ $serieDefault->nombre }}
            </span>
          @endif
        </div>
      </div>

      {{-- Fechas --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Fecha <span class="text-red-500">*</span>
          </label>
          <input type="date" wire:model.live="fecha"
                 class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                        shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500
                        @error('fecha') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">
          @error('fecha') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vencimiento</label>
          <input type="date" wire:model.live="vencimiento"
                 class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                        shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
        </div>
      </div>

      {{-- Forma de pago / Plazo --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Forma de pago</label>
          <select wire:model.live="tipo_pago" wire:change="aplicarFormaPago($event.target.value)"
                  class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                         shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
            <option value="contado">Contado</option>
            <option value="credito">Crédito</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plazo (días)</label>
          <input type="number" min="1" wire:model.live.debounce.250ms="plazo_dias"
                 class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                        shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500
                        disabled:opacity-50" @if($tipo_pago!=='credito') disabled @endif>
        </div>
      </div>

      {{-- Términos / Notas --}}
      <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Términos</label>
          <input type="text" wire:model.live.debounce.400ms="terminos_pago" placeholder="Condiciones, notas de crédito…"
                 class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                        shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
          <input type="text" wire:model.live.debounce.400ms="notas" placeholder="Observaciones visibles en el documento"
                 class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                        shadow-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
        </div>
      </div>
    </div>

    {{-- ============== DETALLES (LÍNEAS) ============== --}}
    <div class="mt-1 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow p-4">
      <div class="flex items-center justify-between mb-3">
        <div>
          <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Líneas de la factura</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">Selecciona producto, bodega y valores.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <button type="button" wire:click="anular" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white">
            <i class="fa-solid fa-ban mr-2"></i> Anular
          </button>
          <button type="button" wire:click="abrirPagos" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white">
            <i class="fa-solid fa-cash-register mr-2"></i> Pagos
          </button>
          <button type="button" wire:click="emitir" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white">
            <i class="fa-solid fa-stamp mr-2"></i> Emitir
          </button>
          <button type="button" wire:click="guardar" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white">
            <i class="fa-solid fa-floppy-disk mr-2"></i> Guardar
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
            <tr>
              <th class="px-4 py-2 text-left">Producto</th>
              <th class="px-4 py-2 text-left">Descripción</th>
              <th class="px-4 py-2 text-left">Bodega</th>
              <th class="px-4 py-2 text-right">Cant.</th>
              <th class="px-4 py-2 text-right">Precio</th>
              <th class="px-4 py-2 text-right">Desc %</th>
              <th class="px-4 py-2 text-right">IVA %</th>
              <th class="px-4 py-2 text-right">IVA $</th>
              <th class="px-4 py-2 text-right">Total línea</th>
              <th class="px-4 py-2 text-right">Acciones</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($lineas as $i => $l)
              @php
                $ivaMonto = \App\Helpers\Money::ivaImporte($l);
                $totalLin = \App\Helpers\Money::totalLinea($l);
              @endphp
              <tr wire:key="linea-{{ $i }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                {{-- Producto --}}
                <td class="px-4 py-2 min-w-[240px]">
                  <select
                    wire:model.live="lineas.{{ $i }}.producto_id"
                    wire:change="setProducto({{ $i }}, $event.target.value)"
                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                           shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                    <option value="">— Seleccione —</option>
                    @foreach($productos as $p)
                      <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                    @endforeach
                  </select>

                  {{-- Badge impuesto del producto --}}
                  @php
                    $prodSel = !empty($l['producto_id']) ? $productos->firstWhere('id', $l['producto_id']) : null;
                  @endphp
                  @if($prodSel && $prodSel->impuesto)
                    <div class="mt-1 text-xs">
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full
                                   bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200">
                        {{ $prodSel->impuesto->nombre }}
                        @if(!is_null($prodSel->impuesto->porcentaje))
                          ({{ number_format($prodSel->impuesto->porcentaje,2) }}%)
                        @elseif(!is_null($prodSel->impuesto->monto_fijo))
                          (${{ number_format($prodSel->impuesto->monto_fijo,2) }})
                        @endif
                        @if($prodSel->impuesto->incluido_en_precio) · incluido en precio @endif
                      </span>
                    </div>
                  @endif
                </td>

                {{-- Descripción --}}
                <td class="px-4 py-2">
                  <input type="text" placeholder="Descripción (opcional)"
                         class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                                shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                         wire:model.live.debounce.250ms="lineas.{{ $i }}.descripcion">
                </td>

                {{-- Bodega --}}
                <td class="px-4 py-2 min-w-[160px]">
                  <select class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                                 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                          wire:model.live="lineas.{{ $i }}.bodega_id">
                    <option value="">— Seleccione —</option>
                    @foreach($bodegas as $b)
                      <option value="{{ $b->id }}">{{ $b->nombre }}</option>
                    @endforeach
                  </select>
                </td>

                {{-- Cantidad --}}
                <td class="px-4 py-2 text-right">
                  <input type="number" step="0.001" min="0.001"
                         class="w-28 text-right rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                                shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                         wire:model.live.debounce.200ms="lineas.{{ $i }}.cantidad">
                </td>

                {{-- Precio --}}
                <td class="px-4 py-2 text-right">
                  <input type="number" step="0.01" min="0"
                         class="w-28 text-right rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                                shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                         wire:model.live.debounce.200ms="lineas.{{ $i }}.precio_unitario">
                </td>

                {{-- Descuento % --}}
                <td class="px-4 py-2 text-right">
                  <input type="number" step="0.001" min="0"
                         class="w-24 text-right rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                                shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                         wire:model.live.debounce.200ms="lineas.{{ $i }}.descuento_pct">
                </td>

                {{-- IVA % --}}
                <td class="px-4 py-2 text-right">
                  <input type="number" step="0.001" min="0"
                         class="w-24 text-right rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                                shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                         wire:model.live.debounce.200ms="lineas.{{ $i }}.impuesto_pct">
                </td>

                {{-- IVA $ --}}
                <td class="px-4 py-2 text-right">
                  ${{ number_format($ivaMonto, 2) }}
                </td>

                {{-- Total línea --}}
                <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                  ${{ number_format($totalLin, 2) }}
                </td>

                {{-- Acciones --}}
                <td class="px-4 py-2 text-right">
                  <div class="inline-flex items-center gap-2">
                    <button type="button" wire:click="addLinea"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white">
                      + Línea
                    </button>
                    <button type="button" wire:click="removeLinea({{ $i }})"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-500 hover:bg-red-600 text-white">
                      Quitar
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                  No hay líneas. Agrega la primera con “+ Línea”.
                </td>
              </tr>
            @endforelse
          </tbody>

          {{-- Totales (usando propiedades computadas) --}}
          <tfoot>
            <tr class="bg-gray-50 dark:bg-gray-800/40">
              <td colspan="8"></td>
              <td class="px-4 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Subtotal:</td>
              <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                ${{ number_format($this->subtotal, 2) }}
              </td>
            </tr>
            <tr class="bg-gray-50 dark:bg-gray-800/40">
              <td colspan="8"></td>
              <td class="px-4 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Impuestos:</td>
              <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">
                ${{ number_format($this->impuestosTotal, 2) }}
              </td>
            </tr>
            <tr class="bg-gray-100 dark:bg-gray-800/60">
              <td colspan="8"></td>
              <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-gray-100">Total:</td>
              <td class="px-4 py-2 text-right font-extrabold text-gray-900 dark:text-white">
                ${{ number_format($this->total, 2) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    {{-- ============== LISTADO (si lo usas en esta vista) ============== --}}
    <div class="mt-8">
      <livewire:facturas.lista-facturas />
    </div>

  </div>
</div>
