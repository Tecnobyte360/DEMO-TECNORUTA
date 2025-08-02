<div>
  @if ($mensaje)
    <div class="p-3 mb-4 rounded-lg flex justify-between items-center
        @if ($tipoMensaje === 'success') bg-green-500 text-white
        @elseif ($tipoMensaje === 'error') bg-red-500 text-white
        @elseif ($tipoMensaje === 'warning') bg-yellow-400 text-black
        @else bg-gray-300 text-black @endif
    ">
        <span class="font-semibold">{{ $mensaje }}</span>
        <button wire:click="$set('mensaje', '')" class="font-bold ml-4">X</button>
    </div>
@endif



    <form wire:submit.prevent="guardar" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-black">Nombre</label>
            <input type="text" wire:model="nombre" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
           <label class="block text-sm font-medium text-black">Ubicación</label>
            <input type="text" wire:model="ubicacion" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('ubicacion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center">
            <input type="checkbox" wire:model="activo" class="mr-2">
        <label class="text-sm text-black">Bodega Activa</label>

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
