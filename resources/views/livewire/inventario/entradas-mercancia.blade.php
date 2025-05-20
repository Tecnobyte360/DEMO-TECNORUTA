<form wire:submit.prevent="crearEntrada" class="space-y-12">

    {{-- FORMULARIO DE NUEVA ENTRADA --}}
    <div class="bg-gradient-to-br from-violet-50 to-white dark:from-gray-800 dark:to-gray-900 p-8 rounded-3xl shadow-2xl space-y-10">

        {{-- Encabezado --}}
        <div class="flex items-center justify-between">
            <h2 class="text-4xl font-extrabold text-violet-700 dark:text-white flex items-center gap-3">
                <i class="fas fa-dolly-flatbed"></i> Nueva Entrada
            </h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">Operaciones > Entradas</span>
        </div>

        {{-- Mensajes --}}
        @if (session()->has('message'))
            <div class="p-4 bg-green-500/90 text-white rounded-2xl shadow-lg animate-pulse">
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="p-4 bg-red-500/90 text-white rounded-2xl shadow-lg animate-pulse">
                {{ session('error') }}
            </div>
        @endif

        {{-- DATOS GENERALES --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Fecha Contabilización</label>
                <input type="date" wire:model="fecha_contabilizacion" class="w-full px-4 py-2 rounded-2xl border dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500 transition">
                @error('fecha_contabilizacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Socio de Negocio</label>
                <select wire:model="socio_negocio_id" class="w-full px-4 py-2 rounded-2xl border dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500 transition">
                    <option value="">Seleccione</option>
                    @foreach ($socios as $socio)
                        <option value="{{ $socio->id }}">{{ $socio->razon_social }}</option>
                    @endforeach
                </select>
                @error('socio_negocio_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Lista de Precios</label>
                <input type="text" wire:model="lista_precio" placeholder="(opcional)" class="w-full px-4 py-2 rounded-2xl border dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500 transition">
            </div>
        </div>

        {{-- OBSERVACIONES --}}
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Observaciones</label>
            <textarea wire:model="observaciones" rows="3" class="w-full px-4 py-3 rounded-2xl border dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500 transition"></textarea>
        </div>

        {{-- TABLA DE PRODUCTOS --}}
        <div class="overflow-x-auto rounded-2xl border dark:border-gray-700 shadow-lg">
            <table class="min-w-full bg-white dark:bg-gray-800 rounded-2xl text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-violet-600 text-white uppercase tracking-wider text-xs">
                    <tr>
                        <th class="p-4 text-left">Producto</th>
                        <th class="p-4">Descripción</th>
                        <th class="p-4 text-center">Cantidad</th>
                        <th class="p-4 text-center">Bodega</th>
                        <th class="p-4 text-center">Precio</th>
                        <th class="p-4 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entradas as $index => $entrada)
                        <tr class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="p-4">
                                <select wire:model="entradas.{{ $index }}.producto_id" class="w-full px-2 py-1 rounded-xl dark:bg-gray-800">
                                    <option value="">Seleccione</option>
                                    @foreach ($productos as $producto)
                                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                                    @endforeach
                                </select>
                                @error("entradas.$index.producto_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </td>

                            <td class="p-4">
                                <input type="text" disabled wire:model="entradas.{{ $index }}.descripcion" class="w-full bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-xl">
                            </td>

                            <td class="p-4 text-center">
                                <input type="number" wire:model="entradas.{{ $index }}.cantidad" class="w-20 text-center rounded-xl dark:bg-gray-800">
                            </td>

                            <td class="p-4 text-center">
                                <select wire:model="entradas.{{ $index }}.bodega_id" class="w-full px-2 py-1 rounded-xl dark:bg-gray-800">
                                    <option value="">Seleccione</option>
                                    @foreach ($bodegas as $bodega)
                                        <option value="{{ $bodega->id }}">{{ $bodega->nombre }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td class="p-4 text-center">
                                <input type="number" step="0.01" wire:model="entradas.{{ $index }}.precio_unitario" class="w-24 text-center rounded-xl dark:bg-gray-800">
                            </td>

                            <td class="p-4 text-center">
                                <button wire:click="eliminarFila({{ $index }})" type="button" class="text-red-500 hover:text-red-700 transition">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- BOTONES --}}
        <div class="flex justify-between items-center mt-8">
            <button wire:click="agregarFila" type="button" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-full shadow-md transition transform hover:scale-105">
                + Agregar Producto
            </button>

            <div class="flex gap-4">
                <button wire:click="cancelarEntrada" type="button" class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-full shadow-md transition transform hover:scale-105">
                    Cancelar
                </button>

                <button type="submit" class="px-6 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-full shadow-md transition transform hover:scale-105" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="crearEntrada">Guardar Entrada</span>
                    <span wire:loading wire:target="crearEntrada">
                        Guardando...
                    </span>
                </button>
            </div>
        </div>

    </div>

    {{-- LISTADO DE ENTRADAS REGISTRADAS --}}
    <div class="bg-gradient-to-br from-gray-100 to-white dark:from-gray-800 dark:to-gray-900 p-6 rounded-3xl shadow-2xl space-y-6">

        <h2 class="text-3xl font-extrabold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
            <i class="fas fa-clipboard-list"></i> Entradas Registradas
        </h2>

        <div class="overflow-x-auto rounded-2xl border dark:border-gray-700 shadow-lg">
            <table class="min-w-full bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-200 dark:bg-gray-700 uppercase tracking-wider text-xs">
                    <tr>
                        <th class="p-4 text-left">Fecha</th>
                        <th class="p-4 text-left">Socio</th>
                        <th class="p-4 text-left">Observaciones</th>
                        <th class="p-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entradasMercancia as $entrada)
                        <tr class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="p-4">{{ $entrada->fecha_contabilizacion }}</td>
                            <td class="p-4">{{ $entrada->socioNegocio->razon_social ?? '-' }}</td>
                            <td class="p-4 truncate max-w-sm">{{ $entrada->observaciones }}</td>
                            <td class="p-4 text-center">
                                <button wire:click="verDetalle({{ $entrada->id }})" class="px-4 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-md transition transform hover:scale-105">
                                    Ver Detalle
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

</form>
