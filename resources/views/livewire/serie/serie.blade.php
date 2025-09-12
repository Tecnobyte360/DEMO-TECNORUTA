<div class="p-4 md:p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-xl space-y-4">

  {{-- Encabezado / filtros --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <h1 class="text-xl md:text-2xl font-extrabold text-gray-800 dark:text-white">Series</h1>
      <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Prefijos, rangos y numeración por documento.</p>
    </div>

    <div class="flex items-center gap-2">
      <input type="text" wire:model.live="search" placeholder="Buscar (nombre, prefijo, resolución)…"
             class="px-3 py-2 rounded-md border text-sm dark:bg-gray-800 dark:text-white" />
      <select wire:model.live="perPage" class="px-3 py-2 rounded-md border text-sm dark:bg-gray-800 dark:text-white">
        <option value="10">10</option><option value="20">20</option><option value="50">50</option>
      </select>
      <button wire:click="create"
              class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2.5 text-white font-semibold shadow hover:bg-indigo-700">
        + Nueva serie
      </button>
    </div>
  </div>

  {{-- Tabla --}}
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-100 dark:bg-gray-800/60 text-gray-600 dark:text-gray-300">
        <tr>
          <th class="px-3 py-2 text-left">Documento</th>
          <th class="px-3 py-2 text-left">Nombre</th>
          <th class="px-3 py-2 text-left">Prefijo</th>
          <th class="px-3 py-2 text-left">Rango</th>
          <th class="px-3 py-2 text-left">Long.</th>
          <th class="px-3 py-2 text-left">Próximo</th>
          <th class="px-3 py-2 text-left">Default</th>
          <th class="px-3 py-2 text-left">Vigencia</th>
          <th class="px-3 py-2 text-left">Activo</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200/70 dark:divide-gray-800">
        @forelse($items as $row)
          @php
            $long = $row->longitud ?? 6;
            $n    = max((int)$row->proximo, (int)$row->desde);
            $num  = str_pad((string)$n, $long, '0', STR_PAD_LEFT);
            $proximoFmt = ($row->prefijo ? "{$row->prefijo}-" : '').$num;
          @endphp
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
            <td class="px-3 py-2">{{ strtoupper($row->documento ?? 'factura') }}</td>
            <td class="px-3 py-2 font-medium text-gray-800 dark:text-gray-100">{{ $row->nombre }}</td>
            <td class="px-3 py-2">{{ $row->prefijo ?: '—' }}</td>
            <td class="px-3 py-2">{{ number_format($row->desde) }}–{{ number_format($row->hasta) }}</td>
            <td class="px-3 py-2">{{ $long }}</td>
            <td class="px-3 py-2 font-semibold">{{ $proximoFmt }}</td>
            <td class="px-3 py-2">
              @if($row->es_default ?? false)
                <span class="px-2 py-0.5 text-xs rounded bg-indigo-100 text-indigo-700">Default</span>
              @else
                —
              @endif
            </td>
            <td class="px-3 py-2">
              @if($row->vigente_desde || $row->vigente_hasta)
                {{ optional($row->vigente_desde)->format('Y-m-d') }} → {{ optional($row->vigente_hasta)->format('Y-m-d') }}
              @else
                —
              @endif
            </td>
            <td class="px-3 py-2">
              <button wire:click="toggleActivo({{ $row->id }})"
                      class="px-2 py-1 rounded-md text-xs {{ $row->activa ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                {{ $row->activa ? 'Activo' : 'Inactivo' }}
              </button>
            </td>
            <td class="px-3 py-2 text-right">
              <div class="inline-flex gap-2">
                <button wire:click="edit({{ $row->id }})"
                        class="px-3 py-1.5 rounded-md bg-white dark:bg-gray-800 border hover:bg-gray-50 dark:hover:bg-gray-700">
                  Editar
                </button>
                <button onclick="if(confirm('¿Eliminar la serie?')) Livewire.dispatch('call',{fn:'delete',args:[{{ $row->id }}]})"
                        class="px-3 py-1.5 rounded-md bg-white dark:bg-gray-800 border hover:bg-red-50 text-red-600">
                  Eliminar
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="10" class="px-3 py-6 text-center text-gray-500">Sin resultados…</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="pt-2">
    {{ $items->links() }}
  </div>

  {{-- Modal Crear/Editar --}}
  <div x-data="{ open: @entangle('showModal') }" x-cloak>
    <div class="fixed inset-0 bg-black/40 z-40" x-show="open" x-transition.opacity></div>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-show="open" x-transition>
      <div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-gray-900 shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold">Serie</h2>
          <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800" @click="open=false">✕</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="text-xs text-gray-500">Documento</label>
            <select wire:model.defer="documento" class="w-full px-3 py-2 rounded-md border dark:bg-gray-800 dark:text-white">
              <option value="factura">Factura</option>
              <option value="oferta">Oferta</option>
              <option value="pedido">Pedido</option>
              <option value="nota_credito">Nota crédito</option>
              <option value="otro">Otro</option>
            </select>
            @error('documento') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="text-xs text-gray-500">Nombre</label>
            <input type="text" wire:model.defer="nombre" class="w-full px-3 py-2 rounded-md border dark:bg-gray-800 dark:text-white">
            @error('nombre') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="text-xs text-gray-500">Prefijo</label>
            <input type="text" wire:model.defer="prefijo" class="w-full px-3 py-2 rounded-md border dark:bg-gray-800 dark:text-white">
            @error('prefijo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="text-xs text-gray-500">Desde</label>
            <input type="number" min="1" wire:model.defer="rango_desde" class="w-full px-3 py-2 rounded-md border dark:bg-gray-800 dark:text-white">
            @error('rango_desde') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="text-xs text-gray-500">Hasta</label>
            <input type="number" min="1" wire:model.defer="rango_hasta" class="w-full px-3 py-2 rounded-md border dark:bg-gray-800 dark:text-white">
            @error('rango_hasta') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="text-xs text-gray-500">Longitud</label>
            <input type="number" min="1" max="12" wire:model.defer="longitud" class="w-full px-3 py-2 rounded-md border dark:bg-gray-800 dark:text-white">
            @error('longitud') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="md:col-span-3">
            <label class="text-xs text-gray-500">Resolución (opcional)</label>
            <input type="text" wire:model.defer="resolucion" class="w-full px-3 py-2 rounded-md border dark:bg-gray-800 dark:text-white">
            @error('resolucion') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="text-xs text-gray-500">Vigencia inicio</label>
            <input type="date" wire:model.defer="fecha_inicio" class="w-full px-3 py-2 rounded-md border dark:bg-gray-800 dark:text-white">
            @error('fecha_inicio') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="text-xs text-gray-500">Vigencia fin</label>
            <input type="date" wire:model.defer="fecha_fin" class="w-full px-3 py-2 rounded-md border dark:bg-gray-800 dark:text-white">
            @error('fecha_fin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="flex items-center gap-3 mt-6">
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" wire:model.defer="es_default" class="h-4 w-4">
              <span class="text-sm">Marcar como Default para este documento</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" wire:model.defer="activo" class="h-4 w-4">
              <span class="text-sm">Activo</span>
            </label>
          </div>
        </div>

        <div class="flex items-center justify-between pt-2">
          <div class="text-sm text-gray-600 dark:text-gray-300">
            Próximo: <span class="font-semibold">{{ $this->previewSiguiente($serie_id) ?: $this->previewSiguiente() }}</span>
          </div>
          <div class="flex gap-2">
            <button class="px-4 py-2 rounded-xl border dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800" @click="open=false">Cancelar</button>
            <button wire:click="save" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">Guardar</button>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
