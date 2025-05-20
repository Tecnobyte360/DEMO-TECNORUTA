<!-- Agrega Font Awesome en tu layout si no lo tienes -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" 
     x-bind:class="darkMode ? 'dark' : ''" 
     class="p-4 bg-white dark:bg-gray-900 shadow-md rounded-lg transition-all duration-300">

    <!-- Encabezado -->
    <div class="flex items-center justify-between mb-4 border-b pb-3">
        <h2 class="text-lg sm:text-2xl font-semibold text-gray-700 dark:text-gray-200 flex items-center space-x-2">
            <i class="fas fa-user-shield text-indigo-500"></i>
            <span>Gestión de Usuarios</span>
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm sm:text-base">
            <thead>
                <tr class="bg-gradient-to-r from-violet-500 to-indigo-600 dark:from-indigo-700 dark:to-indigo-900 text-white">
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Nombre</th>
                    <th class="px-4 py-2 text-left hidden sm:table-cell">Email</th>
                    <th class="px-4 py-2 text-center">Contraseña</th>
                    <th class="px-4 py-2 text-center hidden sm:table-cell">Estado</th>
                    <th class="px-4 py-2 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                @foreach ($usuarios as $usuario)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-300 text-center">{{ $usuario->id }}</td>
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-300">{{ $usuario->name }}</td>
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-300 hidden sm:table-cell">{{ $usuario->email }}</td>
                        
                        <!-- Contraseña con Icono de Mostrar/Ocultar -->
                        <td class="px-4 py-2 text-center">
                            <div x-data="{ showPassword: false }" class="relative inline-block">
                                <span x-text="showPassword ? '{{ $usuario->password }}' : '••••••••'" 
                                      class="text-gray-700 dark:text-gray-300 font-mono"></span>
                                <button @click="showPassword = !showPassword" class="ml-2 text-gray-500 hover:text-gray-700">
                                    <i :class="showPassword ? 'fas fa-unlock' : 'fas fa-lock'"></i>
                                </button>
                            </div>
                        </td>

                        <!-- Estado con Switch (Oculto en móvil) -->
                        <td class="px-4 py-2 text-center hidden sm:table-cell">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:click="toggleEstado({{ $usuario->id }})" 
                                    class="sr-only peer" {{ $usuario->activo ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-gray-300 dark:bg-gray-600 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white 
                                    after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border 
                                    after:rounded-full after:h-4 after:w-4 after:transition-all 
                                    peer-checked:bg-green-500 dark:peer-checked:bg-green-400">
                                </div>
                            </label>
                        </td>

                        <!-- Botones de Acción con solo iconos -->
                        <td class="px-4 py-2 flex justify-center space-x-2">
                            <button wire:click="editarUsuario({{ $usuario->id }})"
                                    class="text-blue-500 hover:text-blue-700 transition">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button wire:click="restablecerPassword({{ $usuario->id }})"
                                    class="text-yellow-500 hover:text-yellow-700 transition">
                                <i class="fas fa-key"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
