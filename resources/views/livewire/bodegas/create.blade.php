<div>
    @if ($mensaje)
        <div class="p-3 mb-4 text-white bg-green-500 border border-green-600 rounded-lg flex justify-between items-center">
            <span>{{ $mensaje }}</span>
            <button wire:click="$set('mensaje', '')" class="text-white font-bold">X</button>
        </div>
    @endif

    <form wire:submit.prevent="guardar" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-white">Nombre</label>
            <input type="text" wire:model="nombre" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-white">Ubicación</label>
            <input type="text" wire:model="ubicacion" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('ubicacion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center">
            <input type="checkbox" wire:model="activo" class="mr-2">
            <label class="text-sm text-white">Bodega Activa</label>
        </div>

        <div class="flex justify-end space-x-2">
            <button type="button" wire:click="dispatch('cerrarModal')" class="px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Cancelar
            </button>
            <button type="submit" class="px-3 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600">
                {{ $bodega_id ? 'Actualizar' : 'Guardar' }} <!-- Cambiar texto del botón dependiendo si es edición o creación -->
            </button>
        </div>
    </form>
</div>
