<div class="p-7 bg-white dark:bg-gray-900 rounded-2xl shadow-xl space-y-10">

  <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-10">

    <!-- Datos Generales del Producto -->
    @if($erroresFormulario)
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <strong class="font-bold">¡Corrige los errores!</strong>
        <span class="block sm:inline">Por favor completa los campos requeridos antes de guardar.</span>
      </div>
    @endif

    <section class="space-y-6 p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
      <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Registrar nuevos productos</h3>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <!-- Nombre -->
        <div class="relative">
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Nombre *</label>
          <input 
            wire:model.lazy="nombre" 
            type="text" 
            placeholder="Nombre del producto"
            class="w-full px-4 py-2 rounded-xl border 
              @error('nombre') border-red-500 
              @elseif(!$errors->has('nombre') && !empty($nombre)) border-green-500 
              @else border-gray-300 @enderror 
              dark:border-gray-700 dark:bg-gray-800 dark:text-white 
              focus:ring-2 focus:ring-violet-600 focus:outline-none pr-10"
          />
          @if(!$errors->has('nombre') && !empty($nombre))
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <i class="fas fa-check-circle text-green-500"></i>
            </div>
          @endif
          @error('nombre') 
            <span class="text-red-600 text-xs">{{ $message }}</span> 
          @enderror
        </div>

        <!-- Descripción -->
        <div class="relative">
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Descripción</label>
          <input 
            wire:model.lazy="descripcion" 
            type="text" 
            placeholder="Descripción del producto"
            class="w-full px-4 py-2 rounded-xl border 
              @error('descripcion') border-red-500 
              @elseif(!empty($descripcion)) border-green-500 
              @else border-gray-300 @enderror
              dark:border-gray-700 dark:bg-gray-800 dark:text-white 
              focus:ring-2 focus:ring-violet-600 focus:outline-none pr-10"
          />
          @if(!empty($descripcion) && !$errors->has('descripcion'))
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <i class="fas fa-check-circle text-green-500"></i>
            </div>
          @endif
          @error('descripcion') 
            <span class="text-red-600 text-xs">{{ $message }}</span> 
          @enderror
        </div>

        <!-- Subcategoría -->
        <div class="relative">
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Subcategoría *</label>
          <select 
            wire:model.lazy="subcategoria_id"
            class="w-full px-4 py-2 rounded-xl border 
              @error('subcategoria_id') border-red-500 
              @elseif(!empty($subcategoria_id)) border-green-500 
              @else border-gray-300 @enderror
              dark:border-gray-700 dark:bg-gray-800 dark:text-white 
              focus:ring-2 focus:ring-violet-600 focus:outline-none pr-10"
          >
            <option value="">-- Selecciona Subcategoría --</option>
            @foreach($subcategorias as $sub)
              <option value="{{ $sub->id }}">{{ $sub->nombre }}</option>
            @endforeach
          </select>
          @if(!empty($subcategoria_id) && !$errors->has('subcategoria_id'))
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <i class="fas fa-check-circle text-green-500"></i>
            </div>
          @endif
          @error('subcategoria_id') 
            <span class="text-red-600 text-xs">{{ $message }}</span> 
          @enderror
        </div>

        <!-- Precio (sin IVA) -->
        <div class="relative">
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Precio (sin IVA) *</label>
          <input 
            wire:model.lazy="precio" 
            type="number" 
            step="0.01"
            placeholder="Precio de venta"
            class="w-full px-4 py-2 rounded-xl border 
              @error('precio') border-red-500 
              @elseif(!empty($precio)) border-green-500 
              @else border-gray-300 @enderror
              dark:border-gray-700 dark:bg-gray-800 dark:text-white 
              focus:ring-2 focus:ring-violet-600 focus:outline-none pr-10"
          />
          @if(!empty($precio) && !$errors->has('precio'))
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <i class="fas fa-check-circle text-green-500"></i>
            </div>
          @endif
          @error('precio') 
            <span class="text-red-600 text-xs">{{ $message }}</span> 
          @enderror
        </div>

        <!-- IVA / Impuesto -->
        <div class="relative">
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">IVA / Impuesto</label>
          <select 
            wire:model="impuesto_id"
            class="w-full px-4 py-2 rounded-xl border 
              @error('impuesto_id') border-red-500 
              @elseif(!is_null($impuesto_id)) border-green-500 
              @else border-gray-300 @enderror
              dark:border-gray-700 dark:bg-gray-800 dark:text-white 
              focus:ring-2 focus:ring-violet-600 focus:outline-none pr-10">
            <option value="">— Sin impuesto —</option>
            @foreach($impuestos as $imp)
              <option value="{{ $imp->id }}">
                {{ $imp->nombre }}
                @if(!is_null($imp->porcentaje))
                  ({{ number_format($imp->porcentaje, 2) }}%)
                @elseif(!is_null($imp->monto_fijo))
                  (${{ number_format($imp->monto_fijo, 2) }})
                @endif
              </option>
            @endforeach
          </select>
         {{-- Preview del precio con IVA en tiempo real (no guarda en BD) --}}
@if(($precio ?? null) !== null && $precio !== '')
  <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
    Precio con IVA: <strong>${{ number_format($this->precioConIvaTmp, 2) }}</strong>
  </p>
@endif

          @error('impuesto_id') 
            <span class="text-red-600 text-xs">{{ $message }}</span> 
          @enderror

          {{-- Preview precio con IVA (cuando estás editando y ya existe el producto) --}}
          @if($isEdit && $producto_id)
            @php
              $p = $productos->firstWhere('id', $producto_id);
            @endphp
            @if($p)
              <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                Precio actual con IVA: <strong>${{ number_format($p->precio_con_iva, 2) }}</strong>
              </p>
            @endif
          @endif
        </div>

        <!-- Costo (opcional) -->
        <div class="relative">
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Costo</label>
          <input 
            wire:model.lazy="costo" 
            type="number" 
            step="0.01"
            placeholder="Costo del producto"
            class="w-full px-4 py-2 rounded-xl border 
              @error('costo') border-red-500 
              @elseif(!is_null($costo)) border-green-500 
              @else border-gray-300 @enderror
              dark:border-gray-700 dark:bg-gray-800 dark:text-white 
              focus:ring-2 focus:ring-violet-600 focus:outline-none pr-10"
          />
          @error('costo') 
            <span class="text-red-600 text-xs">{{ $message }}</span> 
          @enderror
        </div>

        <!-- Stock Global Mínimo -->
        <div class="relative">
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Stock Mínimo Global</label>
          <input 
            wire:model.lazy="stockMinimoGlobal" 
            type="number" 
            placeholder="Mínimo todas las bodegas"
            class="w-full px-4 py-2 rounded-xl border 
              @error('stockMinimoGlobal') border-red-500 
              @elseif(!is_null($stockMinimoGlobal)) border-green-500 
              @else border-gray-300 @enderror
              dark:border-gray-700 dark:bg-gray-800 dark:text-white 
              focus:ring-2 focus:ring-violet-600 focus:outline-none pr-10"
          />
          @if(!is_null($stockMinimoGlobal) && !$errors->has('stockMinimoGlobal'))
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <i class="fas fa-check-circle text-green-500"></i>
            </div>
          @endif
          @error('stockMinimoGlobal') 
            <span class="text-red-600 text-xs">{{ $message }}</span> 
          @enderror
        </div>

        <!-- Stock Global Máximo -->
        <div class="relative">
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Stock Máximo Global</label>
          <input 
            wire:model.lazy="stockMaximoGlobal" 
            type="number" 
            placeholder="Máximo todas las bodegas"
            class="w-full px-4 py-2 rounded-xl border 
              @error('stockMaximoGlobal') border-red-500 
              @elseif(!is_null($stockMaximoGlobal)) border-green-500 
              @else border-gray-300 @enderror
              dark:border-gray-700 dark:bg-gray-800 dark:text-white 
              focus:ring-2 focus:ring-violet-600 focus:outline-none pr-10"
          />
          @if(!is_null($stockMaximoGlobal) && !$errors->has('stockMaximoGlobal'))
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
              <i class="fas fa-check-circle text-green-500"></i>
            </div>
          @endif
          @error('stockMaximoGlobal') 
            <span class="text-red-600 text-xs">{{ $message }}</span> 
          @enderror
        </div>

        <!-- Estado switches -->
        <div class="flex flex-wrap items-center gap-6 col-span-full pt-4">
          @foreach (['Activo' => 'activo'] as $label => $model)
            <label class="flex items-center space-x-3">
              <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
              <div class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" wire:model.lazy="{{ $model }}" class="sr-only peer">
                <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-violet-600 transition"></div>
                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></div>
              </div>
            </label>
          @endforeach
        </div>

      </div>
    </section>

    <!-- Stock por Bodega -->
    <section class="space-y-6 p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
      <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Stock Mínimo/Máximo por Bodega</h3>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Seleccionar Bodega</label>
          <select wire:model="bodegaSeleccionada"
            class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none">
            <option value="">-- Selecciona Bodega --</option>
            @foreach($bodegas as $bodega)
              <option value="{{ $bodega->id }}">{{ $bodega->nombre }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Stock Mínimo</label>
          <input wire:model="stockMinimo" type="number" placeholder="Stock mínimo"
            class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none"/>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Stock Máximo</label>
          <input wire:model="stockMaximo" type="number" placeholder="Stock máximo"
            class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none"/>
        </div>
      </div>

      <div class="flex justify-end gap-4 pt-4">
        <!-- Botón: Agregar Bodega -->
        <button type="button" wire:click="agregarBodega"
          class="flex items-center gap-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-xl shadow hover:shadow-md transition">
          <i class="fas fa-plus text-sm"></i>
          <span>Agregar</span>
        </button>

        <!-- Botón: Guardar / Actualizar -->
        <button type="submit"
          class="flex items-center gap-1 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold rounded-xl shadow hover:shadow-md transition">
          <i class="fas fa-save text-sm"></i>
          <span>{{ $isEdit ? 'Actualizar' : 'Guardar' }}</span>
        </button>
      </div>

      @if($stocksPorBodega)
        <div class="overflow-x-auto mt-6">
          <table class="min-w-full text-sm">
            <thead class="bg-violet-600 text-white">
              <tr>
                <th class="p-2 text-left">Bodega</th>
                <th class="p-2 text-center">Stock Mínimo</th>
                <th class="p-2 text-center">Stock Máximo</th>
                <th class="p-2 text-center">Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
              @foreach($stocksPorBodega as $bodegaId => $datos)
                <tr>
                  <td class="p-2">{{ $bodegas->find($bodegaId)->nombre ?? 'Bodega' }}</td>
                  <td class="p-2 text-center">
                    <input type="number" wire:model.defer="stocksPorBodega.{{ $bodegaId }}.stock_minimo"
                      class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none"/>
                  </td>
                  <td class="p-2 text-center">
                    <input type="number" wire:model.defer="stocksPorBodega.{{ $bodegaId }}.stock_maximo"
                      class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none"/>
                  </td>
                  <td class="p-2 text-center">
                    <button type="button" wire:click="eliminarBodega({{ $bodegaId }})"
                      class="flex items-center gap-2 text-red-600 hover:text-red-800 font-bold transition">
                      <i class="fas fa-trash-alt"></i> 
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif

    </section>

  </form>

  <!-- Tabla de productos -->
  <section class="space-y-6 p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">

    <!-- Encabezado + Buscador -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-4">
      <h3 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">Inventario Actual</h3>

      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
        <input 
          wire:model.defer="search"
          type="text"
          placeholder="Buscar producto..."
          class="w-full sm:w-64 px-4 py-2 border rounded-xl dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-violet-600 focus:border-violet-600"
        />
        <button 
          wire:click="$refresh"
          class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-xl shadow transition-all text-sm">
          <i class="fas fa-search mr-1"></i> Buscar
        </button>
      </div>
    </div>

    <!-- Tabla responsive -->
    <div class="w-full overflow-x-auto">
      <table class="min-w-[1100px] w-full bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden text-sm text-gray-700 dark:text-gray-300">
        <thead class="bg-violet-600 text-white">
          <tr>
            <th class="p-3 text-left font-semibold">#ID</th>
            <th class="p-3 text-left font-semibold">Nombre</th>
            <th class="p-3 text-left font-semibold">Descripción</th>
            <th class="p-3 text-center font-semibold">Precio (sin IVA)</th>
            <th class="p-3 text-center font-semibold">Impuesto</th>            {{-- NUEVO --}}
            <th class="p-3 text-center font-semibold">Precio c/ IVA</th>       {{-- NUEVO --}}
            <th class="p-3 text-center font-semibold">Subcategoría</th>
            <th class="p-3 text-center font-semibold">Stock Total</th>
            <th class="p-3 text-center font-semibold">Estado</th>
            <th class="p-3 text-center font-semibold">Alerta</th>
            <th class="p-3 text-center font-semibold">Bodegas</th>
            <th class="p-3 text-center font-semibold">Acciones</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
          @forelse($productos as $prod)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
              <td class="p-3">{{ $prod->id }}</td>
              <td class="p-3">{{ $prod->nombre }}</td>
              <td class="p-3">{{ $prod->descripcion }}</td>
              <td class="p-3 text-center">{{ number_format($prod->precio, 2) }}</td>
              <td class="p-3 text-center">
                {{ $prod->impuesto?->nombre ?? '—' }}
                @if($prod->impuesto)
                  @if(!is_null($prod->impuesto->porcentaje))
                    ({{ number_format($prod->impuesto->porcentaje, 2) }}%)
                  @elseif(!is_null($prod->impuesto->monto_fijo))
                    (${{ number_format($prod->impuesto->monto_fijo, 2) }})
                  @endif
                @endif
              </td>
              <td class="p-3 text-center">
                ${{ number_format($prod->precio_con_iva, 2) }}
              </td>
              <td class="p-3 text-center">{{ $prod->subcategoria->nombre ?? '-' }}</td>
              <td class="p-3 text-center">
                {{ $prod->bodegas->sum(fn($b) => $b->pivot->stock) }}
              </td>

              <td class="p-3 text-center">
                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full {{ $prod->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                  @if ($prod->activo)
                    <i class="fas fa-check-circle"></i> Activo
                  @else
                    <i class="fas fa-times-circle"></i> Inactivo
                  @endif
                </span>
              </td>

              <td class="p-3 text-center">
                @php
                  $bodegasSinStock = [];
                  $bodegasStockBajo = [];
                  $bodegasSobreStock = [];

                  foreach ($prod->bodegas as $bodega) {
                    $stock = $bodega->pivot->stock;
                    $stock_minimo = $bodega->pivot->stock_minimo;
                    $stock_maximo = $bodega->pivot->stock_maximo;

                    if ($stock == 0) {
                      $bodegasSinStock[] = $bodega->nombre;
                    } elseif ($stock < $stock_minimo) {
                      $bodegasStockBajo[] = $bodega->nombre;
                    } elseif (!is_null($stock_maximo) && $stock > $stock_maximo) {
                      $bodegasSobreStock[] = $bodega->nombre;
                    }
                  }

                  $status = 'Abastecido';
                  $color = 'green';
                  $icon = 'fas fa-check-circle';
                  $tooltip = "{$prod->nombre} abastecido en todas las bodegas.";

                  if (count($bodegasSinStock) > 0) {
                    $status = 'Sin stock';
                    $color = 'red';
                    $icon = 'fas fa-times-circle';
                    $tooltip = "{$prod->nombre} sin inventario en " . implode(', ', $bodegasSinStock) . ".";
                  } elseif (count($bodegasStockBajo) > 0) {
                    $status = 'Stock bajo';
                    $color = 'yellow';
                    $icon = 'fas fa-exclamation-triangle';
                    $tooltip = "{$prod->nombre} bajo stock en " . implode(', ', $bodegasStockBajo) . ".";
                  } elseif (count($bodegasSobreStock) > 0) {
                    $status = 'Sobre stock';
                    $color = 'blue';
                    $icon = 'fas fa-boxes';
                    $tooltip = "{$prod->nombre} sobre stock en " . implode(', ', $bodegasSobreStock) . ".";
                  }
                @endphp

                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-700 rounded-full cursor-pointer" title="{{ $tooltip }}">
                  <i class="{{ $icon }} mr-1"></i> {{ $status }}
                </span>
              </td>

              <td class="p-3 text-center">
                @if ($prod->bodegas && $prod->bodegas->count())
                  <button wire:click="$toggle('mostrarBodegas.{{ $prod->id }}')" class="text-indigo-600 hover:text-indigo-800 text-lg" title="Ver bodegas">
                    <i class="fas fa-eye"></i>
                  </button>
                @else
                  <span class="text-gray-400 italic text-xs">Sin Bodegas</span>
                @endif
              </td>

              <td class="p-3 text-center space-x-2">
                <button wire:click="edit({{ $prod->id }})" class="text-blue-600 hover:text-blue-800 transition-colors" title="Editar">
                  <i class="fas fa-edit"></i>
                </button>
              </td>
            </tr>

            {{-- Bodegas detalle --}}
            @if (!empty($mostrarBodegas[$prod->id]) && $prod->bodegas)
              <tr>
                <td colspan="12" class="bg-gray-100 dark:bg-gray-700 p-4">
                  <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-gray-700 dark:text-gray-300">
                      <thead class="bg-violet-200 dark:bg-violet-700 text-gray-800 dark:text-white">
                        <tr>
                          <th class="p-2 text-left">Código</th>
                          <th class="p-2 text-left">Bodega</th>
                          <th class="p-2 text-center">Stock</th>
                          <th class="p-2 text-center">Stock Mínimo</th>
                          <th class="p-2 text-center">Stock Máximo</th>
                          <th class="p-2 text-center">Condición</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-300 dark:divide-gray-600">
                        @foreach($prod->bodegas as $bodega)
                          <tr>
                            <td class="p-2">{{ $bodega->id }}</td>
                            <td class="p-2">{{ $bodega->nombre }}</td>
                            <td class="p-2 text-center">{{ $bodega->pivot->stock }}</td>
                            <td class="p-2 text-center">{{ $bodega->pivot->stock_minimo }}</td>
                            <td class="p-2 text-center">{{ $bodega->pivot->stock_maximo ?? '-' }}</td>
                            <td class="p-2 text-center">
                              @php
                                $stock = $bodega->pivot->stock;
                                $minimo = $bodega->pivot->stock_minimo;
                                $maximo = $bodega->pivot->stock_maximo;
                              @endphp

                              @if ($stock == 0)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-full">
                                  <i class="fas fa-times-circle mr-1"></i> Sin stock
                                </span>
                              @elseif ($stock < $minimo)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full">
                                  <i class="fas fa-exclamation-triangle mr-1"></i> Stock bajo
                                </span>
                              @elseif (!is_null($maximo) && $stock > $maximo)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                  <i class="fas fa-boxes mr-1"></i> Sobre stock
                                </span>
                              @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                                  <i class="fas fa-check-circle mr-1"></i> Abastecido
                                </span>
                              @endif
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </td>
              </tr>
            @endif
          @empty
            <tr>
              <td colspan="12" class="p-4 text-center text-gray-500 dark:text-gray-400 italic">No hay productos registrados.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Escuchar evento cuando producto creado
  window.addEventListener('producto-creado', event => {
    Swal.fire({
      icon: 'success',
      title: '¡Éxito!',
      text: event.detail.mensaje,
      timer: 2000,
      showConfirmButton: false
    });
  });

  // Escuchar evento cuando producto actualizado
  window.addEventListener('producto-actualizado', event => {
    Swal.fire({
      icon: 'success',
      title: '¡Actualizado!',
      text: event.detail.mensaje,
      timer: 2000,
      showConfirmButton: false
    });
  });

  // Escuchar evento cuando producto eliminado
  window.addEventListener('producto-eliminado', event => {
    Swal.fire({
      icon: 'success',
      title: '¡Eliminado!',
      text: event.detail.mensaje,
      timer: 2000,
      showConfirmButton: false
    });
  });

  // Escuchar errores generales
  window.addEventListener('error', event => {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: event.detail.mensaje,
    });
  });
</script>
