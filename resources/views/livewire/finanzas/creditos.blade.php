@assets
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
@endassets
<div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-xl space-y-8 transition-all duration-300">

    <!-- ENCABEZADO -->
    <div class="flex justify-between items-center border-b pb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-file-invoice-dollar text-indigo-600"></i> Órdenes de Venta
        </h1>
    </div>

    <!-- FILTROS -->
    <div class="mb-6 space-y-4 md:space-y-0 md:grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

        <!-- Filtro por estado -->
        <div class="flex flex-col">
            <label for="estadoFiltro" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
            <select wire:model="estadoFiltro" id="estadoFiltro"
                    class="rounded-lg text-sm px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:outline-none">
                <option value="todos">Todos</option>
                <option value="pendiente">Pendientes</option>
                <option value="parcial">Parciales</option>
                <option value="pagado">Pagados</option>
            </select>
        </div>

        <!-- Filtro por cliente -->
        <div class="flex flex-col">
            <label for="filtroCliente" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
            <input type="text" wire:model.debounce.500ms="filtroCliente" id="filtroCliente"
                   placeholder="Buscar por nombre..."
                   class="rounded-lg text-sm px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:outline-none w-full">
        </div>

        <!-- Filtro por tipo de pago -->
        <div class="flex flex-col">
            <label for="tipoPagoFiltro" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Pago</label>
            <select wire:model="tipoPagoFiltro" id="tipoPagoFiltro"
                    class="rounded-lg text-sm px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:outline-none">
                <option value="todos">Todos</option>
                <option value="credito">Crédito</option>
                <option value="contado">Contado</option>
                <option value="transferencia">Transferencia</option>
            </select>
        </div>

        <!-- Filtro por fechas -->
      <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-lg space-y-4">
    <!-- Filtros de fecha -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Fecha Inicio -->
        <div class="flex flex-col">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha de Inicio</label>
            <input type="date" wire:model.defer="fechaInicio"
                   class="rounded-xl text-sm px-4 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition duration-150 ease-in-out">
        </div>

        <!-- Fecha Fin -->
        <div class="flex flex-col">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha de Fin</label>
            <input type="date" wire:model.defer="fechaFin"
                   class="rounded-xl text-sm px-4 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition duration-150 ease-in-out">
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="flex flex-col md:flex-row justify-end gap-2 pt-2">
        <!-- Botón Buscar -->
        <button type="button"
                wire:click="cargarPedidosCredito"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-emerald-500 to-emerald-700 hover:from-emerald-600 hover:to-emerald-800 text-white rounded-xl text-sm font-semibold shadow transition duration-300">
            <i class="fas fa-search"></i> Buscar
        </button>

        <!-- Botón Limpiar -->
        <button type="button"
                wire:click="$set('fechaInicio', null); $set('fechaFin', null); $set('estadoFiltro', 'todos'); $set('tipoPagoFiltro', 'todos'); $set('filtroCliente', '')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-br from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 text-white rounded-xl text-sm font-semibold shadow transition duration-300">
            <i class="fas fa-sync-alt"></i> Limpiar Filtros
        </button>
    </div>
</div>



    </div>




    <!-- MENSAJES -->
    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-800 rounded-xl shadow">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 bg-red-100 text-red-800 rounded-xl shadow">
            {{ session('error') }}
        </div>
    @endif

    <!-- TABLA DE PEDIDOS -->
    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow-md">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 text-sm">
    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white">
        <tr>
            <th class="px-4 py-2 text-left">#Pedido</th>
            <th class="px-4 py-2 text-left">Ruta</th>
            <th class="px-4 py-2 text-left">Cliente</th>
            <th class="px-4 py-2 text-left">Generado por</th>
            <th class="px-4 py-2 text-left">Fecha</th>
            <th class="px-4 py-2 text-right">Total Aplicado</th>
            <th class="px-4 py-2 text-left">Pagado</th>
            <th class="px-4 py-2 text-left">Saldo</th>
            <th class="px-4 py-2 text-left">A Liquidar</th>
            <th class="px-4 py-2 text-left">Tipo Pago</th>
            <th class="px-4 py-2 text-left">Estado</th>
    
            <th class="px-4 py-2 text-left">Acciones</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        @foreach($pedidosCredito as $pedido)
            @php
                $totalBase = $pedido->detalles->sum(fn($d) => $d->cantidad * ($d->producto->precio ?? 0));
                $totalAplicado = $pedido->detalles->sum(fn($d) => $d->cantidad * ($d->precio_unitario));
                $pagado = $pedido->pagos->sum('monto');
                $saldo  = $totalAplicado - $pagado;

                $claveGasto = ($pedido->ruta_id ?? 0) . '_' . $pedido->fecha->format('Y-m-d');
                $gastoRuta = $gastosAgrupados[$claveGasto] ?? collect();
                $montoGasto = $gastoRuta->sum('monto');
                $totalLiquidarPedido = $saldo - $montoGasto;

                $estado = match(true) {
                    $pagado == 0            => 'Pendiente',
                    $pagado < $totalAplicado => 'Parcial',
                    default                 => 'Pagado'
                };
            @endphp
            <tr>
                <td class="px-4 py-2 font-bold">{{ '#'.$pedido->id }}</td>
                <td class="px-4 py-2">{{ $pedido->ruta->ruta ?? 'NO DEFINIDA' }}</td>
                <td class="px-4 py-2">{{ $pedido->socioNegocio->razon_social }}</td>
                <td class="px-4 py-2">{{ $pedido->conductor->name ?? 'Sin asignar' }}</td>
                <td class="px-4 py-2">{{ $pedido->fecha->format('d/m/Y') }}</td>
                <td class="px-4 py-2 text-right">
                    ${{ number_format($totalAplicado, 0, ',', '.') }}
                </td>
                <td class="px-4 py-2 text-green-600">
                    ${{ number_format($pagado, 0, ',', '.') }}
                </td>
                <td class="px-4 py-2 text-red-500">
                    ${{ number_format($saldo, 0, ',', '.') }}
                </td>
                <td class="px-4 py-2 font-bold text-indigo-600">
                    ${{ number_format($totalLiquidarPedido, 0, ',', '.') }}
                </td>
                <td class="px-4 py-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full shadow-sm {{ $pedido->tipo_pago === 'credito' 
                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' 
                        : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' }}">
                        <i class="fas {{ $pedido->tipo_pago === 'credito' ? 'fa-credit-card' : 'fa-money-bill-wave' }}"></i>
                        {{ ucfirst($pedido->tipo_pago) }}
                    </span>
                </td>
                <td class="px-4 py-2 font-medium {{ $estado === 'Pagado' ? 'text-green-600' : ($estado === 'Parcial' ? 'text-yellow-600' : 'text-red-500') }}">
                    {{ $estado }}
                </td>

                

                <!-- Acciones -->
                <td class="px-4 py-2 space-x-2">
                    @if($estado !== 'Pagado')
                        <button wire:click="abrirModalPago({{ $pedido->id }})"
                                class="text-xs px-2 py-1 rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200">
                            Registrar Pago
                        </button>
                    @else
                        <span class="text-xs text-gray-400 italic">Completo</span>
                    @endif

                    <button wire:click="verDetalle({{ $pedido->id }})"
                            class="text-xs px-2 py-1 rounded-lg bg-yellow-100 text-yellow-700 hover:bg-yellow-200">
                        Ver Detalle
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-4 px-4">
{{ $pedidosCredito->links() }}
</div>



    </div>

    <!-- MODAL REGISTRO DE PAGO -->
@if($showPagoModal && $pedidoSeleccionado)
<div class="fixed inset-0 bg-gradient-to-br from-white/70 via-gray-100/80 to-white/70 dark:from-gray-800/70 dark:via-gray-900/70 dark:to-gray-800/70 backdrop-blur-md flex items-center justify-center z-50 transition-all duration-300 ease-in-out">
    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-2xl w-full max-w-md">
        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">
            Registrar Pago - Pedido #{{ $pedidoSeleccionado->id }}
        </h2>

       <form wire:submit.prevent="registrarPago"
      x-data="{
          monto: @entangle('montoPago').defer,
          get montoFormateado() {
              if (this.monto === '' || isNaN(this.monto)) return '';
              return new Intl.NumberFormat('es-CO', {
                  style: 'currency',
                  currency: 'COP',
                  minimumFractionDigits: 0
              }).format(this.monto);
          }
      }"
      class="space-y-4"
>

    <!-- Campo de entrada -->
    <div>
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Monto a pagar</label>
        <input type="number" x-model="monto"
               class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:ring-indigo-500 focus:outline-none focus:ring-2 text-sm"
               step="0.01" min="0">
        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            💲 <span x-text="montoFormateado"></span>
        </div>
        @error('montoPago') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <!-- Botones -->
    <div class="flex justify-end gap-4">
        <button type="button" wire:click="$set('showPagoModal', false)"
                class="px-4 py-2 text-sm rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600">
            Cancelar
        </button>
        <button type="button"
                x-on:click="$wire.set('montoPago', monto).then(() => $wire.registrarPago())"
                class="px-4 py-2 text-sm rounded bg-indigo-600 hover:bg-indigo-700 text-white shadow">
            Confirmar Pago
        </button>
    </div>
</form>

    </div>
</div>
@endif
@if($mostrarDetalle && $pedidoDetalleSeleccionado)
<div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 transition-all duration-300">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-4xl p-6 space-y-6">
        <div class="flex justify-between items-center border-b pb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                Detalle del Pedido #{{ $pedidoDetalleSeleccionado->id }}
            </h2>
            <button wire:click="$set('mostrarDetalle', false)" class="text-gray-500 hover:text-red-600">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300">
            <div><strong>Cliente:</strong> {{ $pedidoDetalleSeleccionado->socioNegocio->razon_social }}</div>
            <div><strong>Ruta:</strong> {{ $pedidoDetalleSeleccionado->ruta->ruta ?? 'NO DEFINIDA' }}</div>
            <div><strong>Conductor:</strong> {{ $pedidoDetalleSeleccionado->conductor->name ?? 'Sin asignar' }}</div>
            <div><strong>Fecha:</strong> {{ $pedidoDetalleSeleccionado->fecha->format('d/m/Y') }}</div>
        </div>

        <div class="overflow-x-auto rounded-xl">
            <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Producto</th>
                        <th class="px-4 py-2 text-center">Cantidad</th>
                        <th class="px-4 py-2 text-right">Precio</th>
                        <th class="px-4 py-2 text-left">Lista Aplicada</th>   {{-- nueva columna --}}
                        <th class="px-4 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                    <tbody>
                    @foreach($pedidoDetalleSeleccionado->detalles as $detalle)
                        <tr>
                        <td class="px-4 py-2">{{ $detalle->producto->nombre }}</td>
                        <td class="px-4 py-2 text-center">{{ $detalle->cantidad }}</td>
                        <td class="px-4 py-2 text-right">
                            ${{ number_format($detalle->precio_unitario,0,',','.') }}
                        </td>
                        <td class="px-4 py-2 text-left">
                            {{ optional($detalle->precioLista)->nombre ?? 'Precio Base' }}
                        </td>
                        <td class="px-4 py-2 text-right">
                            ${{ number_format($detalle->cantidad * $detalle->precio_unitario,0,',','.') }}
                        </td>
                        </tr>
                    @endforeach
                    </tbody>

            </table>
        </div>

        <div class="flex justify-end pt-2">
            <button wire:click="$set('mostrarDetalle', false)"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm rounded shadow">
                Cerrar
            </button>
        </div>
    </div>
</div>
@endif


</div>
