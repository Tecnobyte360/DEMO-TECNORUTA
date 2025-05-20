<div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl space-y-8">

    {{-- Navegación de pestañas --}}
    <div>
        <nav class="flex space-x-6 border-b-2 border-gray-200 dark:border-gray-700">
            <button
                wire:click="$set('tab', 'entradas')"
                class="py-2 px-4 text-sm font-bold focus:outline-none {{ $tab === 'entradas' ? 'border-b-4 border-violet-600 text-violet-600' : 'text-gray-600 dark:text-gray-400 hover:text-violet-600' }}"
            >
                ➡️ Entradas Mercancía
            </button>

            <button
                wire:click="$set('tab', 'listado')"
                class="py-2 px-4 text-sm font-bold focus:outline-none {{ $tab === 'listado' ? 'border-b-4 border-violet-600 text-violet-600' : 'text-gray-600 dark:text-gray-400 hover:text-violet-600' }}"
            >
                📜 Salidas Mercancia
            </button>
        </nav>
    </div>

    {{-- Contenido dinámico según la pestaña activa --}}
    <div class="mt-6">
        @if ($tab === 'entradas')
            <livewire:inventario.entradas-mercancia />
        @elseif ($tab === 'listado')
            <livewire:inventario.lista-entradas-mercancia />
        @endif
    </div>

</div>
