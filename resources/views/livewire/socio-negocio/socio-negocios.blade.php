
@assets
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
@endassets
<div x-data="{ showCreateModal: false, showEditModal: false, showImportModal: false }"
     x-init="console.log('Alpine.js cargado')"
     wire:init="loadSocios"
     class="p-6 bg-white dark:bg-gray-900 shadow-md rounded-lg transition-all duration-300">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6 border-b pb-3">
        <h2 class="text-xl sm:text-2xl font-semibold text-gray-700 dark:text-gray-200 flex items-center space-x-2">
            <i class="fas fa-users-cog text-indigo-500"></i>
            <span >Gestión de Socios de Negocio</span>
        </h2>
        <div class="flex space-x-4">
            <button @click="showCreateModal = true" class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition flex items-center space-x-2">
                <i class="fas fa-plus"></i> <span>Nuevo Socio</span>
            </button>
          <button @click="showImportModal = true" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition flex items-center space-x-2">
            <i class="fas fa-users"></i> <span>Importar socios de negocio</span>
        </button>

        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg shadow-sm">
        <table class="min-w-full text-sm sm:text-base border-collapse">
            <thead>
                <tr class="bg-gradient-to-r from-violet-500 to-indigo-600 dark:from-indigo-700 dark:to-indigo-900 text-white">
                    <th class="px-4 py-2 text-left">Id</th>
                    <th class="px-4 py-2 text-left">Razón Social</th>
                    <th class="px-4 py-2 text-left">NIT</th>
                    <th class="px-4 py-2 text-left">Dirección</th>
                    <th class="px-4 py-2 text-center">Saldo Pendiente</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                @foreach ($socioNegocios as $socio)
                    <tr class="hover:bg-gray-50 transition" wire:key='{{$socio->id}}'>
                        <td class="px-4 py-2">{{ ucwords(strtolower($socio->id)) }}</td>
                        <td class="px-4 py-2">{{ ucwords(strtolower($socio->razon_social)) }}</td>
                        <td class="px-4 py-2">{{ ucwords(strtolower($socio->nit)) }}</td>
                        <td class="px-4 py-2">{{ ucwords(strtolower($socio->direccion)) }}</td>
                        <td class="px-4 py-2 text-center">{{ number_format($socio->saldo_pendiente, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-center">
                           <button wire:click="editsocio({{ $socio->id }})" @click="showEditModal = true" 
                                    wire:loading.attr="disabled" class="text-blue-500 hover:text-blue-700">
                                <i class="fas fa-edit"></i>
                            </button>


                            <button wire:click="delete({{ $socio->id }})" class="text-red-500 hover:text-red-700 transition p-2 rounded-lg" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>


        </table>
    </div>

    <!-- Modal Crear Socio -->
    <div class="fixed inset-0 flex items-center justify-center bg-opacity-50 backdrop-blur-sm z-50"
        x-show="showCreateModal" x-cloak
        @keydown.escape.window="showCreateModal = false"
        @close-modal.window="showCreateModal = false"
        wire:ignore.self>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Crear Nuevo Socio</h3>
                <button @click="showCreateModal = false" class="text-gray-600 dark:text-gray-400 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @livewire('socio-negocio.create')
        </div>
    </div>




    <!-- Modal Importar Excel -->
    <div class="fixed inset-0 flex items-center justify-center bg-opacity-50 backdrop-blur-sm z-50"
         x-show="showImportModal" x-cloak
         @keydown.escape.window="showImportModal = false"
         @close-modal.window="showImportModal = false"
         wire:ignore.self>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Importar Socios de Negocio</h3>
                <button @click="showImportModal = false" class="text-gray-600 dark:text-gray-400 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @livewire('socio-negocio.importar-excel')
        </div>
    </div>

    <!-- Modal Editar Socio -->
   <!-- Modal Editar Socio -->
    <div class="fixed inset-0 flex items-center justify-center bg-opacity-50 backdrop-blur-sm z-50"
        x-show="showEditModal" x-cloak
        @keydown.escape.window="showEditModal = false"
        @close-modal.window="showEditModal = false"
        wire:ignore.self>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Editar Socio</h3>
                <button @click="showEditModal = false" class="text-gray-600 dark:text-gray-400 hover:text-red-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
    @livewire('socio-negocio.edit')

        </div>
    </div>

</div>
