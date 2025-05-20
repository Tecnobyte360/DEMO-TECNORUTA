<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div x-data="{ showCreateModal: false, showEditModal: false }"
     x-init="console.log('Alpine.js cargado')"
     wire:init="loadRoles"
     class="p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg transition-all duration-300">

    <div class="flex items-center justify-between mb-4 border-b pb-3">
        <h2 class="text-lg sm:text-2xl font-semibold text-gray-700 dark:text-gray-200 flex items-center space-x-2">
            <i class="fas fa-users-cog text-indigo-500"></i>
            <span>Gestión de bodegas</span>
        </h2>
        <button @click="showCreateModal = true" class="px-3 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition">
            <i class="fas fa-plus"></i> Nueva Bodega
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm sm:text-base">
            <thead>
                <tr class="bg-gradient-to-r from-violet-500 to-indigo-600 dark:from-indigo-700 dark:to-indigo-900 text-white">
                    <th class="px-4 py-2 text-left">Id</th>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left">Ubicación</th>
                    <th class="px-4 py-2 text-center">Estado</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                @foreach ($bodegas as $bodega)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2">{{ $bodega->id }}</td>
                        <td class="px-4 py-2">{{ $bodega->nombre }}</td>
                        <td class="px-4 py-2">{{ $bodega->ubicacion }}</td>
                        <td class="px-4 py-2 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer"
                                       x-data="{ activo: {{ $bodega->activo ? 'true' : 'false' }} }"
                                       x-model="activo"
                                       @change="$wire.toggleEstado({{ $bodega->id }})"
                                       :checked="activo">
                                <div class="w-11 h-6 bg-gray-300 peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer
                                            peer-checked:after:translate-x-full peer-checked:after:border-white
                                            after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300
                                            after:border after:rounded-full after:h-5 after:w-5 after:transition-all
                                            peer-checked:bg-green-600">
                                </div>
                            </label>
                        </td>

                        <td class="px-4 py-2 flex justify-center space-x-2">
                            <button wire:click="editar({{ $bodega->id }})" 
                                    @click="$dispatch('open-edit-modal')" 
                                    class="text-blue-500 hover:text-blue-700 transition p-2 rounded-lg" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button wire:click="eliminar({{ $bodega->id }})" class="text-red-500 hover:text-red-700 transition p-2 rounded-lg" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Crear -->
    <div 
        class="fixed inset-0 flex items-center justify-center bg-opacity-50 backdrop-blur-sm z-50"
        x-show="showCreateModal" 
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        @keydown.escape.window="showCreateModal = false"
        @close-modal.window="showCreateModal = false" 
        wire:ignore.self
    >
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Crear Nueva Bodega</h3>
                <button @click="showCreateModal = false" class="text-gray-600 dark:text-gray-400 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @livewire('bodegas.create')
        </div>
    </div>

    <!-- Modal Editar -->
    <div 
        x-data
        @open-edit-modal.window="showEditModal = true"
        class="fixed inset-0 flex items-center justify-center bg-opacity-50 backdrop-blur-sm z-50"
        x-show="showEditModal" 
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        @keydown.escape.window="showEditModal = false"
        @close-modal.window="showEditModal = false" 
        wire:ignore.self
    >
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Editar Bodega</h3>
                <button @click="showEditModal = false" class="text-gray-600 dark:text-gray-400 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @livewire('bodegas.edit')
        </div>
    </div>

</div>
