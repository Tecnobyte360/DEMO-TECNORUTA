<div class="p-8 bg-white dark:bg-gray-900 rounded-3xl shadow-2xl space-y-12">
    {{-- Encabezado --}}
    <header class="text-center space-y-2">
        <h2 class="text-3xl font-extrabold text-gray-800 dark:text-white">Administrar Subcategorías</h2>
    </header>

    {{-- Formulario --}}
    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}" class="space-y-10">
        <section class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Nombre --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 dark:text-gray-300 block mb-1">Nombre</label>
                    <input wire:model="nombre" type="text" placeholder="Nombre"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none" />
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 dark:text-gray-300 block mb-1">Descripción</label>
                    <input wire:model="descripcion" type="text" placeholder="Descripción"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none" />
                </div>

                {{-- Categoría --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 dark:text-gray-300 block mb-1">Categoría</label>
                    <select wire:model="categoria_id"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none">
                        <option value="">-- Selecciona Categoría --</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Switch Activo y Botón --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 pt-2">
                <div>
                    <label class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2 block">Estado</label>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Inactivo</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="activo" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-violet-500 dark:bg-gray-700 rounded-full peer dark:peer-focus:ring-violet-600 peer-checked:bg-violet-600 transition-all"></div>
                            <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5"></div>
                        </label>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Activo</span>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white text-base font-semibold rounded-2xl shadow-md hover:shadow-lg transition-all">
                        <i class="fas fa-save"></i>
                        {{ $isEdit ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </section>
    </form>

    {{-- Tabla de subcategorías --}}
    <section class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 rounded-2xl shadow-md overflow-hidden text-sm text-gray-700 dark:text-gray-300">
            <thead class="bg-violet-600 text-white">
                <tr>
                    <th class="p-4 text-left font-semibold">#ID</th>
                    <th class="p-4 text-left font-semibold">Nombre</th>
                    <th class="p-4 text-left font-semibold">Descripción</th>
                    <th class="p-4 text-left font-semibold">Categoría</th>
                    <th class="p-4 text-center font-semibold">Estado</th>
                    <th class="p-4 text-center font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($subcategorias as $sub)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="p-4">{{ $sub->id }}</td>
                        <td class="p-4">{{ $sub->nombre }}</td>
                        <td class="p-4">{{ $sub->descripcion }}</td>
                        <td class="p-4 text-gray-600 dark:text-gray-400">{{ $sub->categoria->nombre ?? '-' }}</td>
                        <td class="p-4 text-center">
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full {{ $sub->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $sub->activo ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="p-4 text-center space-x-2">
                            <button wire:click="edit({{ $sub->id }})" class="text-blue-600 hover:text-blue-800 transition-colors" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>