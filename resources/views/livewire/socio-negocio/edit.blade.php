<div class="w-full px-4 sm:px-6 lg:px-8">
  <div class="mx-auto max-w-md md:max-w-lg lg:max-w-2xl xl:max-w-4xl bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">

    {{-- Mensajes de sesión --}}
    @if (session()->has('message'))
      <div class="p-3 mb-4 text-white bg-green-500 border border-green-600 rounded-lg flex justify-between items-center">
        <span>{{ session('message') }}</span>
        <button wire:click="$set('message','')" class="text-white font-bold">X</button>
      </div>
    @endif

    @if (session()->has('error'))
      <div class="p-3 mb-4 text-white bg-red-500 border border-red-600 rounded-lg flex justify-between items-center">
        <span>{{ session('error') }}</span>
        <button wire:click="$set('error','')" class="text-white font-bold">X</button>
      </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Razón Social --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Razón Social</label>
          <input type="text" wire:model.defer="razon_social"
            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" >
          @error('razon_social') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- NIT --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">NIT/Cédula</label>
          <input type="text" wire:model.defer="nit" oninput="this.value = this.value.replace(/[^\d]/g, '')"
            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" >
          @error('nit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Tipo --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Tipo</label>
          <select wire:model.live="tipo"
            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" >
            <option value="">Selecciona</option>
            <option value="C">C</option>
            <option value="P">P</option>
            <option value="Ambos">Ambos</option>
          </select>
          @error('tipo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Teléfono Fijo --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Teléfono Fijo</label>
          <input type="text" wire:model.defer="telefono_fijo" oninput="this.value = this.value.replace(/[^\d]/g, '')"
            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
          @error('telefono_fijo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Teléfono Móvil --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Teléfono Móvil</label>
          <input type="text" wire:model.defer="telefono_movil" oninput="this.value = this.value.replace(/[^\d]/g, '')"
            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
          @error('telefono_movil') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Correo --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Correo</label>
          <input type="email" wire:model.defer="correo"
            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" >
          @error('correo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Dirección --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Dirección</label>
          <input type="text" wire:model.defer="direccion"
            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600" >
          @error('direccion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Municipio/Barrio --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Municipio/Barrio</label>
          <input type="text" wire:model.defer="municipio_barrio"
            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
          @error('municipio_barrio') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- Saldo Pendiente --}}
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Saldo Pendiente</label>
          <input type="number" wire:model.defer="saldo_pendiente" min="0" step="0.01"
            class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
          @error('saldo_pendiente') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
      </div>

      <div class="flex justify-end space-x-3">
        <button type="submit"
          class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600">
          Actualizar
        </button>
      </div>
    </form>
  </div>
</div>
