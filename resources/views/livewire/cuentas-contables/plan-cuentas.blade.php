<div class="p-4 md:p-6">
  <style>
    /* Líneas de árbol estilo SAP */
    .sap-tree-row { position: relative; }
    .sap-indent {
      position: relative; height: 100%; display: inline-block;
    }
    .sap-indent::before {
      content: ""; position: absolute; top: -14px; bottom: -14px; left: 9px;
      border-left: 1px dashed var(--tw-prose-borders, rgba(107,114,128,.25));
    }
    .sap-connector {
      width: 18px; height: 18px; border-bottom: 1px dashed rgba(107,114,128,.25);
      margin-right: .25rem;
    }
  </style>

  <div class="rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-gray-900">
    <div class="grid grid-cols-1 lg:grid-cols-12">

      {{-- ===== Panel IZQUIERDO: Ficha ===== --}}
      <aside class="lg:col-span-3 p-4 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-800">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            Detalle de cuenta
          </h3>
          <button type="button" wire:click="openCreate({{ $selectedId ?? 'null' }})"
                  class="text-xs px-2 py-1 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white">
            + Nueva
          </button>
        </div>

        <div class="space-y-3 text-sm">
          <label class="block">
            <span class="text-[11px] text-gray-500">Cuenta de mayor</span>
            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-2 py-1"
                   wire:model="f_codigo" readonly>
          </label>
          <label class="block">
            <span class="text-[11px] text-gray-500">Nombre</span>
            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-2 py-1"
                   wire:model="f_nombre" readonly>
          </label>
          <label class="block">
            <span class="text-[11px] text-gray-500">Moneda</span>
            <input type="text" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-2 py-1"
                   wire:model="f_moneda" readonly>
          </label>

          <div class="grid grid-cols-2 gap-2">
            <div class="flex items-center justify-between">
              <span class="text-[11px] text-gray-500">Requiere tercero</span>
              <input type="checkbox" class="h-4 w-4" wire:model="f_requiere_tercero" disabled>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-[11px] text-gray-500">Cuenta activa</span>
              <input type="checkbox" class="h-4 w-4" wire:model="f_cuenta_activa" disabled>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <label class="block">
              <span class="text-[11px] text-gray-500">Nivel</span>
              <input type="number" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-2 py-1"
                     wire:model="f_nivel" readonly>
            </label>
            <div class="flex items-center justify-between">
              <span class="text-[11px] text-gray-500">Título</span>
              <input type="checkbox" class="h-4 w-4" wire:model="f_titulo" disabled>
            </div>
          </div>
        </div>

        <div class="mt-4 flex gap-2">
          <button type="button" wire:click="expandAll"
                  class="px-3 py-1.5 text-xs rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
            Expandir
          </button>
          <button type="button" wire:click="collapseAll"
                  class="px-3 py-1.5 text-xs rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
            Colapsar
          </button>
        </div>
      </aside>

      {{-- ===== Panel CENTRAL: Árbol estilo SAP ===== --}}
      <main class="lg:col-span-7 p-4">
        {{-- Barra de filtros --}}
        <div class="flex items-end gap-3 mb-3">
          <label class="block">
            <span class="block text-[11px] text-gray-500">Buscar</span>
            <input type="text" wire:model.debounce.400ms="q" placeholder="Código o nombre…"
                   class="w-64 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-1.5">
          </label>

          <label class="block">
            <span class="block text-[11px] text-gray-500">Nivel</span>
            <select wire:model="nivelMax"
                    class="w-28 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-1.5">
              @for($i=1;$i<=10;$i++)
                <option value="{{ $i }}">{{ $i }}</option>
              @endfor
              <option value="">Todos</option>
            </select>
          </label>

          <div class="ml-auto text-[11px] text-gray-500 dark:text-gray-400">
            Nivel actual: <span class="font-semibold">{{ $nivelMax ?? 'Todos' }}</span>
          </div>
        </div>

        {{-- Tabla/árbol --}}
        <div class="border border-gray-200 dark:border-gray-800 rounded-md overflow-hidden">
          <div class="bg-slate-100/70 dark:bg-gray-800 px-3 py-2 text-[12px] font-medium text-gray-700 dark:text-gray-300 flex">
            <div class="w-44">Cuenta</div>
            <div class="flex-1">Descripción</div>
            <div class="w-28">Naturaleza</div>
            <div class="w-16 text-center">Activa</div>
            <div class="w-24 text-right pr-2">Saldo</div>
          </div>

          <div class="max-h-[65vh] overflow-auto text-sm">
            @forelse($items as $row)
              @php
                $expanded = in_array($row->id, $this->expandidos);
                $hasKids  = $row->hijos()->exists();
                $indent   = max(0, ($row->nivel_visual - 1) * 18);
              @endphp

              <div class="sap-tree-row flex items-center px-3 py-1.5 border-t border-gray-100 dark:border-gray-800 hover:bg-slate-50 dark:hover:bg-gray-800/50
                          {{ $selectedId===$row->id ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                {{-- Código / caret / icono --}}
                <div class="w-44 font-mono text-[13px] text-gray-800 dark:text-gray-100">
                  {{ $row->codigo }}
                </div>

                {{-- Nombre + árbol --}}
                <div class="flex-1 flex items-center">
                  <span class="sap-indent" style="width: {{ $indent }}px"></span>

                  @if($hasKids)
                    <button type="button" wire:click="toggle({{ $row->id }})"
                            class="mr-1 inline-flex h-5 w-5 items-center justify-center rounded border border-gray-300 dark:border-gray-700
                                   bg-white/60 dark:bg-gray-900/60 hover:bg-white dark:hover:bg-gray-800">
                      <i class="fa-solid fa-caret-{{ $expanded ? 'down' : 'right' }}"></i>
                    </button>
                  @else
                    <span class="sap-connector"></span>
                  @endif

                  <i class="fa-regular {{ $row->titulo ? 'fa-folder' : 'fa-file-lines' }} mr-2 text-sky-700 dark:text-sky-400"></i>

                  <button type="button" wire:click="select({{ $row->id }})"
                          class="text-left flex-1 truncate {{ $row->titulo ? 'italic text-gray-700 dark:text-gray-300' : 'text-gray-900 dark:text-gray-100' }}">
                    {{ $row->nombre }}
                    @if($row->titulo)
                      <span class="ml-2 text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Título</span>
                    @endif
                  </button>

                  {{-- Acciones discretas --}}
                  <div class="ml-2 hidden sm:flex items-center gap-1">
                    <button type="button" title="Añadir hija" wire:click="openCreate({{ $row->id }})"
                            class="px-2 py-1 text-[11px] rounded bg-violet-600 hover:bg-violet-700 text-white">Hija</button>
                    <button type="button" title="Editar" wire:click="openEdit({{ $row->id }})"
                            class="px-2 py-1 text-[11px] rounded bg-indigo-600 hover:bg-indigo-700 text-white">Editar</button>
                  </div>
                </div>

                {{-- Naturaleza --}}
                <div class="w-28">
                  <span class="inline-block text-[11px] px-2 py-0.5 rounded-full
                    @class([
                      'bg-emerald-100 text-emerald-700' => $row->naturaleza==='ACTIVOS',
                      'bg-rose-100 text-rose-700'       => $row->naturaleza==='PASIVOS',
                      'bg-sky-100 text-sky-700'         => $row->naturaleza==='PATRIMONIO',
                      'bg-amber-100 text-amber-700'     => $row->naturaleza==='INGRESOS',
                      'bg-indigo-100 text-indigo-700'   => $row->naturaleza==='COSTOS',
                      'bg-gray-200 text-gray-800'       => $row->naturaleza==='GASTOS',
                      'bg-purple-100 text-purple-700'   => !in_array($row->naturaleza, ['ACTIVOS','PASIVOS','PATRIMONIO','INGRESOS','COSTOS','GASTOS']),
                    ])">
                    {{ $row->naturaleza }}
                  </span>
                </div>

                {{-- Activa --}}
                <div class="w-16 text-center">
                  @if($row->cuenta_activa)
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                  @else
                    <i class="fa-regular fa-circle text-gray-400"></i>
                  @endif
                </div>

                {{-- Saldo --}}
                <div class="w-24 text-right pr-2 font-mono">${{ number_format($row->saldo, 2) }}</div>
              </div>
            @empty
              <div class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">Sin resultados.</div>
            @endforelse
          </div>
        </div>
      </main>

      {{-- ===== Panel DERECHO: Filtros por Naturaleza ===== --}}
      <aside class="lg:col-span-2 p-2 border-t lg:border-t-0 lg:border-l border-gray-200 dark:border-gray-800">
        @php
          $cajones = [
            'ACTIVOS' => 'Activos',
            'PASIVOS' => 'Pasivos',
            'PATRIMONIO' => 'Patrimonio',
            'INGRESOS' => 'Ingresos Operacionales',
            'COSTOS' => 'Costos Operacionales',
            'GASTOS' => 'Gastos Operacionales',
            'OTROS_INGRESOS' => 'Otros Ingresos',
            'OTROS_GASTOS' => 'Otros Gastos',
          ];
        @endphp

        <div class="flex flex-col gap-2">
          <button type="button" wire:click="setNaturaleza('TODAS')"
                  class="w-full text-left rounded-lg px-3 py-2 border {{ $naturaleza==='TODAS' ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200' }}">
            Todas
          </button>
          @foreach($cajones as $key => $label)
            <button type="button" wire:click="setNaturaleza('{{ $key }}')"
                    class="w-full text-left rounded-lg px-3 py-2 border {{ $naturaleza===$key ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200' }}">
              {{ $label }}
            </button>
          @endforeach
        </div>
      </aside>
    </div>
  </div>

  {{-- ================== MODAL CREAR/EDITAR ================== --}}
  @if($showModal)
    <div class="fixed inset-0 z-40 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" wire:click="$set('showModal', false)"></div>

      <div class="relative z-50 w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-xl">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
            {{ $editingId ? 'Editar cuenta' : 'Nueva cuenta' }}
          </h3>
          <button class="text-gray-500 hover:text-gray-700" wire:click="$set('showModal', false)">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form wire:submit.prevent="save" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
          {{-- Padre --}}
          <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Cuenta padre</label>
            <select wire:model="padre_id"
                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                           shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
              <option value="">— Sin padre (raíz) —</option>
              @foreach($posiblesPadres as $p)
                <option value="{{ $p->id }}">{{ $p->codigo }} — {{ $p->nombre }}</option>
              @endforeach
            </select>
            @error('padre_id') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Código --}}
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Código</label>
            <input type="text" wire:model.defer="codigo" placeholder="11050501"
                   class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                          shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 @error('codigo') border-red-500 @enderror">
            @error('codigo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Nombre --}}
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Nombre</label>
            <input type="text" wire:model.defer="nombre" placeholder="CAJA GENERAL"
                   class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                          shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 @error('nombre') border-red-500 @enderror">
            @error('nombre') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Naturaleza --}}
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Naturaleza</label>
            <select wire:model="naturaleza_form"
                    class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                           shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
              <option value="ACTIVOS">ACTIVOS</option>
              <option value="PASIVOS">PASIVOS</option>
              <option value="PATRIMONIO">PATRIMONIO</option>
              <option value="INGRESOS">INGRESOS</option>
              <option value="COSTOS">COSTOS</option>
              <option value="GASTOS">GASTOS</option>
              <option value="OTROS_INGRESOS">OTROS_INGRESOS</option>
              <option value="OTROS_GASTOS">OTROS_GASTOS</option>
            </select>
          </div>

          {{-- Flags --}}
          <div class="grid grid-cols-2 gap-3 md:col-span-2">
            <label class="inline-flex items-center gap-2"><input type="checkbox" wire:model="cuenta_activa" class="rounded"><span class="text-sm">Activa</span></label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" wire:model="titulo" class="rounded"><span class="text-sm">Título (no imputable)</span></label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" wire:model="requiere_tercero" class="rounded"><span class="text-sm">Requiere tercero</span></label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" wire:model="confidencial" class="rounded"><span class="text-sm">Confidencial</span></label>
          </div>

          {{-- Moneda / Saldo --}}
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Moneda</label>
            <input type="text" wire:model.defer="moneda"
                   class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                          shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Saldo inicial</label>
            <input type="number" step="0.01" min="0" wire:model.defer="saldo"
                   class="w-full text-right rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                          shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
          </div>

          {{-- Dimensiones --}}
          <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-4 gap-3">
            <div><label class="block text-[11px] font-medium mb-1">Dimensión 1</label><input type="text" wire:model.defer="dimension1" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2"></div>
            <div><label class="block text-[11px] font-medium mb-1">Dimensión 2</label><input type="text" wire:model.defer="dimension2" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2"></div>
            <div><label class="block text-[11px] font-medium mb-1">Dimensión 3</label><input type="text" wire:model.defer="dimension3" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2"></div>
            <div><label class="block text-[11px] font-medium mb-1">Dimensión 4</label><input type="text" wire:model.defer="dimension4" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2"></div>
          </div>

          {{-- Acciones modal --}}
          <div class="md:col-span-2 flex items-center justify-end gap-2 mt-2">
            <button type="button" wire:click="$set('showModal', false)"
                    class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600">
              Cancelar
            </button>
            <button type="submit"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white">
              {{ $editingId ? 'Actualizar' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  @endif
</div>
