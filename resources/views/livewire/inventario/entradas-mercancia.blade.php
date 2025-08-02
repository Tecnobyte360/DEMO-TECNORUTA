<form wire:submit.prevent="crearEntrada" class="space-y-12">

    <!-- FORMULARIO NUEVA ENTRADA -->
    <section class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-xl space-y-8">

        <!-- ENCABEZADO -->
        <div class="flex justify-between items-center border-b pb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-dolly-flatbed text-violet-600"></i> Nueva Entrada de Mercancía
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">Inventario / Entradas</span>
        </div>

        <!-- MENSAJES -->
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded-xl shadow-sm animate-fade-in">
                <i class="fas fa-check-circle mr-2"></i> {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded-xl shadow-sm animate-fade-in">
                <i class="fas fa-times-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif

        <!-- DATOS GENERALES -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Fecha -->
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Fecha Contabilización *</label>
                <input type="date" wire:model="fecha_contabilizacion"
                    class="w-full px-4 py-2 rounded-xl border @error('fecha_contabilizacion') border-red-500 @else border-gray-300 @enderror dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500">
                @error('fecha_contabilizacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Socio -->
           <div>
    <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Socio de Negocio *</label>

    <div wire:ignore x-data x-init="
        tom = new TomSelect($refs.select, {
            placeholder: 'Seleccione un cliente...',
            allowEmptyOption: true,
            create: false,
            onChange: value => @this.set('socio_negocio_id', value),
        });
        Livewire.hook('message.processed', () => tom.refreshOptions(false));
    ">
        <select x-ref="select" class="w-full px-4 py-2 rounded-xl border @error('socio_negocio_id') border-red-500 @else border-gray-300 @enderror dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500">
            <option value="">Seleccione</option>
            @foreach ($socios as $socio)
                <option value="{{ $socio->id }}">{{ $socio->razon_social }}</option>
            @endforeach
        </select>
    </div>

    @error('socio_negocio_id')
        <span class="text-red-500 text-xs">{{ $message }}</span>
    @enderror
</div>

        </div>

        <!-- Observaciones -->
        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
            <textarea wire:model="observaciones" rows="3"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500"
                placeholder="Agrega observaciones internas si es necesario..."></textarea>
        </div>

        <!-- Tabla productos -->
       <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-md">
        <table class="min-w-full bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-900 text-sm text-gray-700 dark:text-gray-300 rounded-2xl">
            <thead class="bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-white text-xs uppercase tracking-wider">
                <tr>
                        <th class="p-4 text-left"><i class="fas fa-box"></i> Producto</th>
                        <th class="p-4"><i class="fas fa-info-circle"></i> Descripción</th>
                        <th class="p-4 text-center"><i class="fas fa-sort-numeric-up"></i> Cantidad</th>
                        <th class="p-4 text-center"><i class="fas fa-warehouse"></i> Bodega</th>
                        <th class="p-4 text-center"><i class="fas fa-dollar-sign"></i> Costo</th>
                        <th class="p-4 text-center"><i class="fas fa-tools"></i> Acción</th>
                    </tr>
                </thead>
                <tbody>
                  @foreach ($entradas as $index => $entrada)
    <tr class="border-t dark:border-gray-700 hover:bg-violet-100 dark:hover:bg-gray-700 transition">
        <!-- Producto (buscador con datalist) -->
       <td class="p-4">
    <div class="relative">
       
        <input 
            list="productos_list_{{ $index }}" 
            wire:model.lazy="entradas.{{ $index }}.producto_nombre"
            wire:change="actualizarProductoDesdeNombre({{ $index }})"
            placeholder="Buscar producto..." 
            class="w-full px-4 py-2 rounded-xl border 
                @error('entradas.' . $index . '.producto_id') border-red-500 
                @elseif(!empty($entrada['producto_id'])) border-green-500 
                @else border-gray-300 @enderror
                dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600"
        >

        <datalist id="productos_list_{{ $index }}">
            @foreach ($productos as $producto)
                <option value="{{ $producto->nombre }}">{{ $producto->nombre }}</option>
            @endforeach
        </datalist>

        @error("entradas.$index.producto_id")
            <span class="text-red-600 text-xs">{{ $message }}</span>
        @enderror
    </div>
</td>


        <!-- Descripción -->
        <td class="p-4">
            <input 
                type="text" 
                disabled 
                wire:model="entradas.{{ $index }}.descripcion"
                class="w-full bg-gray-100 dark:bg-gray-700 px-4 py-2 rounded-xl text-sm text-gray-700 dark:text-white" 
            />
        </td>

        <!-- Cantidad -->
        <td class="p-4 text-center">
            <div class="relative">
                <input 
                    type="number" 
                    wire:model="entradas.{{ $index }}.cantidad"
                    class="w-24 text-center px-3 py-2 rounded-xl border 
                        @error('entradas.' . $index . '.cantidad') border-red-500 
                        @elseif(!empty($entrada['cantidad'])) border-green-500 
                        @else border-gray-300 @enderror
                        dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600" 
                />
                @if(!empty($entrada['cantidad']) && !$errors->has('entradas.' . $index . '.cantidad'))
                    <div class="absolute right-2 top-1/2 transform -translate-y-1/2">
                        <i class="fas fa-check-circle text-green-500 text-sm"></i>
                    </div>
                @endif
                @error("entradas.$index.cantidad")
                    <span class="text-red-600 text-xs block mt-1">{{ $message }}</span>
                @enderror
            </div>
        </td>

        <!-- Bodega -->
        <td class="p-4 text-center">
            <div class="relative">
                <select wire:model="entradas.{{ $index }}.bodega_id"
                    class="w-full px-4 py-2 rounded-xl border 
                        @error('entradas.' . $index . '.bodega_id') border-red-500 
                        @elseif(!empty($entrada['bodega_id'])) border-green-500 
                        @else border-gray-300 @enderror
                        dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600">
                    <option value="">Seleccione</option>
                    @foreach ($bodegas as $bodega)
                        <option value="{{ $bodega->id }}">{{ $bodega->nombre }}</option>
                    @endforeach
                </select>
                @if(!empty($entrada['bodega_id']) && !$errors->has('entradas.' . $index . '.bodega_id'))
                    <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                        <i class="fas fa-check-circle text-green-500 text-sm"></i>
                    </div>
                @endif
                @error("entradas.$index.bodega_id")
                    <span class="text-red-600 text-xs">{{ $message }}</span>
                @enderror
            </div>
        </td>

        <!-- Precio -->
        <td class="p-4 text-center">
            <div class="relative">
                <input 
                    type="number" 
                    step="0.01" 
                    wire:model="entradas.{{ $index }}.precio_unitario"
                    class="w-24 text-center px-3 py-2 rounded-xl border 
                        @error('entradas.' . $index . '.precio_unitario') border-red-500 
                        @elseif(!empty($entrada['precio_unitario'])) border-green-500 
                        @else border-gray-300 @enderror
                        dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600" 
                />
                @if(!empty($entrada['precio_unitario']) && !$errors->has('entradas.' . $index . '.precio_unitario'))
                    <div class="absolute right-2 top-1/2 transform -translate-y-1/2">
                        <i class="fas fa-check-circle text-green-500 text-sm"></i>
                    </div>
                @endif
                @error("entradas.$index.precio_unitario")
                    <span class="text-red-600 text-xs">{{ $message }}</span>
                @enderror
            </div>
        </td>

        <!-- Acción -->
        <td class="p-4 text-center">
            <button wire:click="eliminarFila({{ $index }})" type="button"
                class="text-red-500 hover:text-red-700 transition transform hover:scale-110">
                <i class="fas fa-trash-alt"></i>
            </button>
        </td>
    </tr>
@endforeach

                </tbody>
            </table>
        </div>

        <!-- Botones -->
        <div class="flex justify-between items-center pt-4">
            <button type="button" wire:click="agregarFila"
                class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-full shadow-md transition transform hover:scale-105">
                <i class="fas fa-plus-circle mr-1"></i> Agregar Producto
            </button>

            <div class="flex gap-4">
                <button type="button" wire:click="cancelarEntrada"
                    class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-full shadow-md transition transform hover:scale-105">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>

                <button type="submit"
                    class="px-6 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-full shadow-md transition transform hover:scale-105"
                    wire:loading.attr="disabled">
                    <i class="fas fa-save mr-1"></i>
                    <span wire:loading.remove wire:target="crearEntrada">Guardar Entrada</span>
                    <span wire:loading wire:target="crearEntrada">Guardando...</span>
                </button>
            </div>
        </div>
    </section>
<section class="mt-10 bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-xl space-y-6">
    <h3 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2 border-b pb-2">
        <i class="fas fa-clipboard-list text-indigo-500"></i> Entradas Registradas
    </h3>

    <div class="overflow-x-auto rounded-2xl border dark:border-gray-700 shadow-md">
        <table class="min-w-full bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-900 text-sm text-gray-700 dark:text-gray-300 rounded-2xl">
            <thead class="bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-white text-xs uppercase tracking-wider">
                <tr>
                    <th class="p-4 text-left"><i class="fas fa-calendar-alt"></i> Fecha</th>
                    <th class="p-4 text-left"><i class="fas fa-user-tie"></i> Socio</th>
                    <th class="p-4 text-left"><i class="fas fa-comment-dots"></i> Observaciones</th>
                    <th class="p-4 text-center"><i class="fas fa-eye"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entradasMercancia as $entrada)
                    <tr class="border-t dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-gray-700 transition">
                        <td class="p-4">{{ $entrada->fecha_contabilizacion }}</td>
                        <td class="p-4">{{ $entrada->socioNegocio->razon_social ?? '-' }}</td>
                        <td class="p-4 truncate max-w-sm">{{ $entrada->observaciones }}</td>
                        <td class="p-4 text-center">
                            <button type="button" wire:click="verDetalle({{ $entrada->id }})"
                                class="px-4 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-sm transition transform hover:scale-105">
                                <i class="fas fa-search"></i> Ver Detalle
                            </button>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500 dark:text-gray-400 italic">
                            <i class="fas fa-info-circle"></i> No hay entradas registradas aún.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
 



</section>
@if ($mostrarDetalle && $detalleEntrada)
   <div 
    x-data="{ mostrarDetalleModal: false }"
 x-init="
    window.addEventListener('abrirModalDetalle', () => {
        mostrarDetalleModal = true;
    });
"
    x-show="mostrarDetalleModal"
    x-transition
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-transparent backdrop-blur-sm"
>


        <div class="bg-white dark:bg-gray-900 w-full max-w-4xl mx-auto rounded-2xl p-6 shadow-2xl relative">

            <!-- Botón cerrar -->
            <button @click="mostrarDetalleModal = false" 
                    class="absolute top-3 right-3 text-red-600 hover:text-red-800 text-xl">
                <i class="fas fa-times-circle"></i>
            </button>

            <h3 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2 mb-4">
                <i class="fas fa-box-open text-indigo-500"></i> 
                Detalle de Entrada #{{ $detalleEntrada->id }}
            </h3>

            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                Socio: <strong>{{ $detalleEntrada->socioNegocio->razon_social ?? '-' }}</strong><br>
                Fecha: <strong>{{ $detalleEntrada->fecha_contabilizacion }}</strong><br>
                Observaciones: <em>{{ $detalleEntrada->observaciones }}</em>
            </p>

            <!-- Tabla de detalles -->
           <div class="overflow-x-auto max-h-96 overflow-y-auto rounded-xl border dark:border-gray-700 shadow-sm">
                <table class="min-w-full text-sm bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-xl overflow-hidden">
                    <thead class="bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-white text-xs uppercase tracking-wider">
                        <tr>
                            <th class="p-3 text-left">Producto</th>
                            <th class="p-3 text-left">Descripción</th>
                            <th class="p-3 text-center">Cantidad</th>
                            <th class="p-3 text-center">Bodega</th>
                            <th class="p-3 text-center">Costo Unitario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($detalleEntrada->detalles as $detalle)
                            <tr>
                                <td class="p-3">{{ $detalle->producto->nombre ?? '-' }}</td>
                                <td class="p-3">{{ $detalle->producto->descripcion ?? '-' }}</td>
                                <td class="p-3 text-center">{{ $detalle->cantidad }}</td>
                                <td class="p-3 text-center">{{ $detalle->bodega->nombre ?? '-' }}</td>
                                <td class="p-3 text-center">${{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-6">
                <button @click="mostrarDetalleModal = false"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
@endif

</form>
