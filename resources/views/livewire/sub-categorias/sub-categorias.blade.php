<div class="p-8 bg-white dark:bg-gray-900 rounded-2xl shadow-xl space-y-10">
    {{-- Encabezado --}}
    <header class="text-center space-y-2">
        <h2 class="text-3xl font-extrabold text-gray-800 dark:text-white">Administrar Categorías</h2>
        <p class="text-gray-500 dark:text-gray-400 text-base">Organiza y clasifica tus productos fácilmente</p>
    </header>

    {{-- Formulario --}}
    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Nombre --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">
                    Nombre
                </label>
                <input
                    wire:model="nombre"
                    type="text"
                    placeholder="Nombre"
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none"
                />
            </div>

            {{-- Descripción --}}
            <div>
                <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">
                    Descripción
                </label>
                <input
                    wire:model="descripcion"
                    type="text"
                    placeholder="Descripción"
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none"
                />
            </div>

            {{-- Categoría y Activo --}}
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                {{-- Select Categoría --}}
                <select
                    wire:model="categoria_id"
                    class="w-full md:w-auto px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none"
                >
                    <option value="">-- Selecciona Categoría --</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                    @endforeach
                </select>

                {{-- Checkbox Activo --}}
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model="activo" class="text-violet-600 rounded focus:ring-violet-500" />
                    Activo
                </label>
            </div>

            {{-- Botón Guardar/Actualizar --}}
            <div class="md:col-span-3 flex justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl shadow transition-all duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $isEdit ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </div>
    </form>

    {{-- Tabla de categorías --}}
    <section class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden text-sm text-gray-700 dark:text-gray-300">
            <thead class="bg-violet-600 text-white">
                <tr>
                    <th class="p-3 text-left font-semibold">Nombre</th>
                          <th class="p-3 text-left font-semibold">Descripción</th>
                    <th class="p-3 text-left font-semibold">Categoría</th>
                    <th class="p-3 text-center font-semibold">Activo</th>
                    <th class="p-3 text-center font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subcategorias as $sub)
                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="p-3">{{ $sub->nombre }}</td>
                           <td class="p-3">{{ $sub->descripcion }}</td>
                        <td class="p-3 text-gray-600 dark:text-gray-400">{{ $sub->categoria->nombre ?? '-' }}</td>
                        <td class="p-3 text-center">
                            <span
                                class="inline-block px-2 py-1 text-xs font-semibold rounded-full {{ $sub->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}"
                            >
                                {{ $sub->activo ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="p-3 text-center space-x-2">
                            <!-- Botón Editar -->
                            <button wire:click="edit({{ $sub->id }})"
                                class="text-blue-600 hover:text-blue-800 transition-colors"
                                title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <!-- Botón Eliminar -->
                            <button wire:click="delete({{ $sub->id }})"
                                class="text-red-600 hover:text-red-800 transition-colors"
                                title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>
