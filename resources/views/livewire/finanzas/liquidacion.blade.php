<div> {{-- ÚNICO elemento raíz para TODO el componente --}}

    <div class="p-8 bg-gradient-to-br from-gray-100 to-white dark:from-gray-900 dark:to-gray-950 rounded-3xl shadow-2xl space-y-10">

        <!-- Encabezado con título y rango de fechas -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-hand-holding-usd text-indigo-500"></i>
                    Informe de Liquidación de Conductores
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Rango:
                    <span class="font-semibold text-indigo-600 dark:text-indigo-400">
                        {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}
                        -
                        {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </span>
                </p>
            </div>
        </div>

    <div x-data="{ mostrarResumen: false }">
    <!-- Botón para mostrar/ocultar -->
    <button
        @click="mostrarResumen = !mostrarResumen"
        class="px-4 py-2 bg-indigo-600 text-white rounded-xl mb-4 shadow hover:bg-indigo-700 transition"
    >
        <span x-show="!mostrarResumen">📊 Mostrar Resumen General</span>
        <span x-show="mostrarResumen">Ocultar Resumen General</span>
    </button>

    <!-- Contenedor que se oculta/muestra -->
    <div
        x-show="mostrarResumen"
        x-transition
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 text-sm font-semibold"
    >
        <!-- 🧾 Deducciones por Ruta y Devoluciones -->
        <div class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 p-4 rounded-2xl shadow text-center">
            🧾 Deducciones por Ruta y Devoluciones:
            <div class="text-lg font-bold mt-1">
                ${{ number_format(
                    collect($resumenConductores)->sum('total_gastos')
                  + collect($resumenConductores)->sum('total_devoluciones')
                , 0, ',', '.') }}
            </div>
        </div>

        <!-- 📦 Total Vendido -->
        <div class="bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white p-4 rounded-2xl shadow text-center">
            📦 Total Vendido:
            <div class="text-lg font-bold mt-1">
                ${{ number_format(
                        collect($resumenConductores)
                            ->sum(fn($f) => ($f['total_facturado'] ?? 0) + ($f['total_pagos'] ?? 0))
                    , 0, ',', '.')
                }}
            </div>
        </div>

        <!-- 🧯 Gastos Ruta -->
        <div class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 p-4 rounded-2xl shadow text-center">
            🧯 Gastos Ruta:
            <div class="text-lg font-bold mt-1">
                ${{ number_format(
                        collect($resumenConductores)->sum('total_gastos')
                    , 0, ',', '.')
                }}
            </div>
        </div>

        <!-- 🔁 Devoluciones -->
        <div class="bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300 p-4 rounded-2xl shadow text-center">
            🔁 Devoluciones:
            <div class="text-lg font-bold mt-1">
                ${{ number_format(
                        collect($resumenConductores)->sum('total_devoluciones')
                    , 0, ',', '.')
                }}
            </div>
        </div>

        <!-- 💵 Total a Liquidar -->
        <div class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 p-4 rounded-2xl shadow text-center">
            💵 Total a Liquidar:
            <div class="text-lg font-bold mt-1">
                ${{ number_format(
                        collect($resumenConductores)->sum('total_liquidar')
                    , 0, ',', '.')
                }}
            </div>
        </div>

        <!-- 📊 Neto Acumulado -->
        <div class="bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300 p-4 rounded-2xl shadow text-center">
            📊 Neto Acumulado:
            <div class="text-lg font-bold mt-1">
                ${{ number_format($netoAcumulado, 0, ',', '.') }}
            </div>
        </div>

        <!-- 🏢 Gastos Administrativos -->
        <div class="bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300 p-4 rounded-2xl shadow text-center">
            🏢 Gastos Administrativos:
            <div class="text-lg font-bold mt-1">
                ${{ number_format($totalGastosAdministrativos, 0, ',', '.') }}
            </div>
        </div>

        <!-- 💰 Utilidad Total -->
        <div class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 p-4 rounded-2xl shadow text-center">
            💰 Utilidad Total:
            <div class="text-lg font-bold mt-1">
                ${{ number_format(
                    collect($resumenConductores)->sum('utilidad')
                , 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>
        <!-- Filtros de fecha (Desde – Hasta) y botón Buscar -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl shadow flex flex-col sm:flex-row sm:items-end gap-6">
            <div class="flex-1">
                <label for="fechaInicio" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Desde</label>
                <input
                    type="date"
                    wire:model="fechaInicio"
                    id="fechaInicio"
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-indigo-500 focus:outline-none focus:ring-2 text-sm"
                >
            </div>

            <div class="flex-1">
                <label for="fechaFin" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Hasta</label>
                <input
                    type="date"
                    wire:model="fechaFin"
                    id="fechaFin"
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-indigo-500 focus:outline-none focus:ring-2 text-sm"
                >
            </div>

            <div>
                <button
                    wire:click="generarResumenConductores"
                    class="px-6 py-2 mt-5 sm:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow transition"
                >
                    Buscar
                </button>
            </div>
        </div>

     

    <!-- ----------------------------------------------------------------- -->
    <!-- Tabla de resumen detallado de cada conductor+fecha                -->
    <!-- ----------------------------------------------------------------- -->
    <div class="p-8 bg-gradient-to-br from-gray-100 to-white dark:from-gray-900 dark:to-gray-950 rounded-3xl shadow-2xl space-y-10">
        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-md">
          <table class="min-w-full rounded-2xl">
  <thead class="bg-gray-300 dark:bg-gray-700 text-xs uppercase">
    <tr>
      <th class="px-6 py-3 text-left">🗓 Fecha</th>
      <th class="px-6 py-3 text-left">Conductor</th>
      <th class="px-6 py-3 text-right">Facturado</th>
      <th class="px-6 py-3 text-right">Gastos</th>
      <th class="p-2 text-right">Utilidad</th>

      <th class="px-6 py-3 text-right">Devoluciones</th>
      <th class="px-6 py-3 text-right">Contado</th>
      <th class="px-6 py-3 text-right">Créditos</th>
      <th class="px-6 py-3 text-right">Transferencias</th>
       <th class="px-6 py-3 text-right">Pagos Anteriores</th>
      <th class="px-6 py-3 text-right">Debe entregar</th>
      <th class="px-6 py-3 text-center">Detalle</th>
    </tr>
  </thead>
  <tbody class="bg-white dark:bg-gray-900 divide-y">
    @forelse($resumenConductores as $idx => $fila)
      <tr class="hover:bg-indigo-50 dark:hover:bg-indigo-900">
        <td class="px-6 py-3">
          {{ \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y') }}
        </td>
        <td class="px-6 py-3 flex items-center gap-2">
          <i class="fas fa-user-circle text-indigo-500"></i>
          {{ $fila['nombre'] }}
        </td>
        <td class="px-6 py-3 text-right">
          ${{ number_format($fila['total_facturado'],0,',','.') }}
        </td>
        <td class="px-6 py-3 text-right text-yellow-600">
          ${{ number_format($fila['total_gastos'],0,',','.') }}
        </td>
      <td class="p-2 text-right text-green-700">
            ${{ number_format($fila['utilidad'] ?? 0, 0, ',', '.') }}
        </td>


        <td class="px-6 py-3 text-right text-pink-600">
          ${{ number_format($fila['total_devoluciones'],0,',','.') }}
        </td>
        <td class="px-6 py-3 text-right text-green-500">
          ${{ number_format($fila['total_pagos_contado'],0,',','.') }}
        </td>
        <td class="px-6 py-3 text-right text-green-700">
          ${{ number_format($fila['total_pagos_credito'],0,',','.') }}
        </td>
        <td class="px-6 py-3 text-right text-teal-600">
          ${{ number_format($fila['total_pagos_transferencia'] ?? 0,0,',','.') }}
        </td>
       <td class="px-6 py-3 text-right text-indigo-500">
    <span
        @if(isset($fila['pagos_credito_anteriores_detalle']) && count($fila['pagos_credito_anteriores_detalle']))
            title="{{ collect($fila['pagos_credito_anteriores_detalle'])->map(fn($p) => 'Pedido #'.$p['pedido_id'].' - '.$p['fecha_pedido'])->implode(' | ') }}"
        @endif
        class="cursor-help"
    >
        ${{ number_format($fila['pagos_credito_anteriores'] ?? 0, 0, ',', '.') }}
    </span>
</td>

 <td class="px-6 py-3 text-right font-bold text-indigo-600">
  ${{ number_format(
      $fila['total_pagos_contado'] 
    - $fila['total_gastos'] 
    - $fila['total_devoluciones'] 
    + $fila['pagos_credito_anteriores']
  , 0, ',', '.') }}
</td>


        <td class="px-6 py-3 text-center">
          <button @click="document.getElementById('detalle-{{ $idx }}').classList.toggle('hidden')">
            <i class="fas fa-chevron-down"></i>
          </button>
        </td>
      </tr>

      {{-- Detalle oculto --}}
      <tr id="detalle-{{ $idx }}" class="hidden bg-white dark:bg-gray-800">
    <td colspan="10" class="px-6 py-4">
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto text-[10px]">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="p-1">Producto</th>
                        <th class="p-1 text-center">Cantidad</th>
                        <th class="p-1 text-right">Costo</th>
                        <th class="p-1 text-right">Precio Base</th>
                        <th class="p-1 text-right">Precio Venta</th>
                        <th class="p-1">Lista</th>
                        <th class="p-1 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fila['detalles'] as $item)
                        <tr class="hover:bg-gray-200 dark:hover:bg-gray-700">
                            <td class="p-1">
                                {{ $item['producto'] }}
                                @if($item['tipo'] === 'devolución')
                                    <span class="text-red-500 text-[9px]">(Devolución)</span>
                                @endif
                            </td>
                            <td class="p-1 text-center">{{ $item['cantidad'] }}</td>
                            <td class="p-1 text-right text-gray-700 dark:text-gray-200">
                                ${{ number_format($item['costo'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="p-1 text-right">
                                ${{ number_format($item['precio_base'], 0, ',', '.') }}
                            </td>
                            <td class="p-1 text-right">
                                ${{ number_format($item['precio_venta'], 0, ',', '.') }}
                            </td>
                            <td class="p-1">{{ $item['lista'] }}</td>
                            <td class="p-1 text-right">
                                ${{ number_format($item['subtotal'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </td>
</tr>

      </tr>
    @empty
      <tr>
        <td colspan="10" class="py-6 text-center italic text-gray-400">
          No hay registros para el rango seleccionado.
        </td>
      </tr>
    @endforelse

    {{-- Totales generales --}}
    @if($resumenConductores->isNotEmpty())
  <tr class="bg-indigo-50 dark:bg-indigo-900 font-semibold text-indigo-800 dark:text-indigo-200">
    <td colspan="6" class="px-6 py-4 text-right">🧾 Total General:</td>
    <td class="px-6 py-4 text-right">
      ${{ number_format(collect($resumenConductores)->sum('total_pagos_contado'),0,',','.') }}
    </td>
    <td class="px-6 py-4 text-right">
      ${{ number_format(collect($resumenConductores)->sum('total_pagos_credito'),0,',','.') }}
    </td>
    <td class="px-6 py-4 text-right">
      ${{ number_format(collect($resumenConductores)->sum('total_pagos_transferencia'),0,',','.') }}
    </td>
    <td class="px-6 py-4 text-right">
      ${{ number_format(collect($resumenConductores)->sum('pagos_credito_anteriores'),0,',','.') }}
    </td>
 <td class="px-6 py-4 text-right text-xl font-extrabold">
  ${{ number_format(
      collect($resumenConductores)->sum('total_pagos_contado')
    + collect($resumenConductores)->sum('pagos_credito_anteriores')
    - collect($resumenConductores)->sum('total_gastos')
    - collect($resumenConductores)->sum('total_devoluciones')
  ,0,',','.') }}
</td>


    <td></td>
  </tr>
@endif

  </tbody>
</table>


        </div>
    </div>

</div>
