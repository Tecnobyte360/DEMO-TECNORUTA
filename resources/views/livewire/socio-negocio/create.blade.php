<div class="w-full px-4 sm:px-6 lg:px-8">
  <div class="mx-auto max-w-md md:max-w-lg lg:max-w-2xl xl:max-w-4xl bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
    {{-- Mensaje de éxito --}}
    @if (session()->has('message'))
      <div class="p-3 mb-4 text-white bg-green-500 border border-green-600 rounded-lg flex justify-between items-center">
        <span>{{ session('message') }}</span>
        <button wire:click="$set('message','')" class="text-white font-bold">X</button>
      </div>
    @endif

    {{-- Mensajes de error generales --}}
  {{-- Mensaje de error --}}
@if (session()->has('error'))
    <div class="p-3 mb-4 text-white bg-red-500 border border-red-600 rounded-lg flex justify-between items-center">
        <span>{{ session('error') }}</span>
        <button wire:click="$set('error','')" class="text-white font-bold">X</button>
    </div>
@endif


    <form wire:submit.prevent="save" class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Razón Social --}}
        <div class="col-span-1 sm:col-span-2 lg:col-span-1">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Razón Social</label>
          <input type="text" wire:model.debounce.500ms="razon_social"
                 class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                 >
          @error('razon_social') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- NIT --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">NIT/Cédula</label>
          <input type="text" wire:model.debounce.500ms="nit" oninput="this.value = this.value.replace(/[^\d]/g, '')"
                 class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                 pattern="^[0-9]{6,20}$">
          @error('nit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Tipo --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tipo</label>
          <select wire:model.debounce.500ms="Tipo"
                  class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                  >
            <option value="">Selecciona</option>
            <option value="C">C</option>
            <option value="P">P</option>
          </select>
          @error('Tipo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Teléfono Fijo --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Teléfono Fijo</label>
          <input type="text" wire:model.debounce.500ms="telefono_fijo" oninput="this.value = this.value.replace(/[^\d]/g, '')"
                 class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                 pattern="^\d{7,15}$">
          @error('telefono_fijo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Teléfono Móvil --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Teléfono Móvil</label>
          <input type="text" wire:model.debounce.500ms="telefono_movil" oninput="this.value = this.value.replace(/[^\d]/g, '')"
                 class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                 pattern="^\d{7,15}$">
          @error('telefono_movil') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Correo --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Correo</label>
          <input type="email" wire:model.debounce.500ms="correo"
                 class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                 >
          @error('correo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Dirección --}}
        <div class="col-span-1 sm:col-span-2 lg:col-span-1">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Dirección</label>
          <input type="text" wire:model.debounce.500ms="direccion"
                 class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600"
                 >
          @error('direccion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Municipio/Barrio --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Municipio/Barrio</label>
          <input type="text" wire:model.debounce.500ms="municipio_barrio"
                 class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
          @error('municipio_barrio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Saldo Pendiente --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Saldo Pendiente</label>
          <input type="number" wire:model.debounce.500ms="saldo_pendiente"
                 class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" 
                 step="0.01" min="0" oninput="this.value = this.value.replace(/[^0-9\.]/g, '')">
          @error('saldo_pendiente') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
      </div>

      <div class="flex justify-end space-x-3">
        <button type="submit"
                class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600">
          Guardar
        </button>
      </div>
    </form>
  </div>
</div>
