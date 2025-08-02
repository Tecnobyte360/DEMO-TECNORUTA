<form wire:submit.prevent="guardarSalida" class="space-y-16">
    <section class="bg-gradient-to-br from-white to-gray-100 dark:from-gray-900 dark:to-gray-800 p-10 rounded-3xl shadow-2xl space-y-10">

        <div class="flex justify-between items-center border-b pb-6">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                <i class="fas fa-truck-loading text-violet-600 text-2xl"></i> Nueva Salida de Mercancía
            </h2>
        </div>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-5 py-3 rounded-2xl shadow-sm animate-fade-in flex items-center gap-2">
                <i class="fas fa-check-circle"></i> <span>{{ session('message') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 px-5 py-3 rounded-2xl shadow-sm animate-fade-in flex items-center gap-2">
                <i class="fas fa-times-circle"></i> <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha *</label>
                <input type="date" wire:model="fecha"
                    class="w-full px-4 py-2 rounded-xl border @error('fecha') border-red-500 @else border-gray-300 @enderror dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500">
                @error('fecha') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Ruta *</label>
                <select wire:model="ruta_id"
                    class="w-full px-4 py-2 rounded-xl border @error('ruta_id') border-red-500 @else border-gray-300 @enderror dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500">
                    <option value="">Seleccione</option>
                    @foreach ($rutas as $ruta)
                        <option value="{{ $ruta->id }}">{{ $ruta->ruta }}</option>
                    @endforeach
                </select>
                @error('ruta_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cliente *</label>
                <select wire:model="socio_negocio_id"
                    class="w-full px-4 py-2 rounded-xl border @error('socio_negocio_id') border-red-500 @else border-gray-300 @enderror dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500">
                    <option value="">Seleccione</option>
                    @foreach ($socios as $socio)
                        <option value="{{ $socio->id }}">{{ $socio->razon_social }}</option>
                    @endforeach
                </select>
                @error('socio_negocio_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
            <textarea wire:model="observaciones" rows="3"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500"
                placeholder="Agrega observaciones internas si es necesario..."></textarea>
        </div>
        @if (!is_null($stockDisponible))
            <div class="text-xs text-gray-600 dark:text-gray-400 pt-1">
                <i class="fas fa-boxes"></i> Stock disponible: <strong>{{ $stockDisponible }}</strong> unidades
            </div>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Bodega *</label>
                <select wire:model="bodega_id" class="w-full rounded-xl border border-gray-300 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500">
                    <option value="">Seleccione bodega</option>
                    @foreach ($bodegas as $bodega)
                        <option value="{{ $bodega->id }}">{{ $bodega->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Producto *</label>
                <select wire:model="producto_id" class="w-full rounded-xl border border-gray-300 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500">
                    <option value="">Seleccione producto</option>
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                    @endforeach
                </select>
   


            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1 space-y-1">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad *</label>
                    <input type="number" wire:model="cantidad" placeholder="Cantidad"
                        class="w-full rounded-xl border border-gray-300 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-500">
                </div>
                <button type="button" wire:click="agregarItem"
                    class="mt-5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-full shadow-md transition transform hover:scale-105">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-md">
            <table class="min-w-full bg-gradient-to-br from-white to-gray-100 dark:from-gray-800 dark:to-gray-900 text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-white text-xs uppercase tracking-wide">
                    <tr>
                        <th class="p-4 text-left"><i class="fas fa-box"></i> Producto</th>
                        <th class="p-4 text-left"><i class="fas fa-warehouse"></i> Bodega</th>
                        <th class="p-4 text-center"><i class="fas fa-sort-numeric-up"></i> Cantidad</th>
                        <th class="p-4 text-center"><i class="fas fa-tools"></i> Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                        <tr class="border-t dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-gray-700 transition">
                            <td class="p-4">{{ $item['producto_nombre'] }}</td>
                            <td class="p-4">{{ $item['bodega_nombre'] }}</td>
                            <td class="p-4 text-center">{{ $item['cantidad'] }}</td>
                            <td class="p-4 text-center">
                                <button wire:click="quitarItem({{ $index }})" type="button"
                                    class="text-red-500 hover:text-red-700 transition transform hover:scale-110">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center pt-6">
            <button type="reset" wire:click="$refresh"
                class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-full shadow-md">
                <i class="fas fa-times mr-1"></i> Cancelar
            </button>

            <button type="submit"
                class="px-6 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-full shadow-md"
                wire:loading.attr="disabled">
                <i class="fas fa-save mr-1"></i>
                <span wire:loading.remove>Guardar Salida</span>
                <span wire:loading>Guardando...</span>
            </button>
        </div>

        <table class="min-w-full bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-900 text-sm text-gray-700 dark:text-gray-300 rounded-2xl">
            <thead class="bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-white text-xs uppercase tracking-wider">
                <tr>
                    <th class="p-4 text-left"><i class="fas fa-calendar-alt"></i> Fecha</th>
                    <th class="p-4 text-left"><i class="fas fa-road"></i> Ruta</th>
                    <th class="p-4 text-left"><i class="fas fa-user-tie"></i> Cliente</th>
                    <th class="p-4 text-left"><i class="fas fa-comment-dots"></i> Observaciones</th>
                    <th class="p-4 text-left"><i class="fas fa-boxes"></i> Productos</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($salidas as $salida)
                    <tr class="border-t dark:border-gray-700 hover:bg-indigo-50 dark:hover:bg-gray-700 transition">
                        <td class="p-4">{{ $salida->fecha }}</td>
                        <td class="p-4">{{ $salida->ruta->ruta ?? '-' }}</td>
                        <td class="p-4">{{ $salida->socioNegocio->razon_social ?? '-' }}</td>
                        <td class="p-4">{{ $salida->observaciones }}</td>
                        <td class="p-4">
                            <ul class="list-disc ml-4">
                                @foreach ($salida->detalles as $detalle)
                                    <li>{{ $detalle->producto->nombre ?? 'Producto' }} ({{ $detalle->cantidad }} und - {{ $detalle->bodega->nombre }})</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500 dark:text-gray-400 italic">
                            <i class="fas fa-info-circle"></i> No hay salidas registradas aún.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
</form>
