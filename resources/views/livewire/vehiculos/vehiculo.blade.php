<div class="p-7 bg-white dark:bg-gray-900 rounded-2xl shadow-xl space-y-10">
    <form wire:submit.prevent="guardarVehiculo" class="space-y-10">

        <section class="space-y-6 p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ $isEdit ? 'Editar Vehículo' : 'Registrar Nuevo Vehículo' }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Placa -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Placa *</label>
                    <input wire:model.lazy="placa" type="text" placeholder="ABC123"
                        class="w-full px-4 py-2 rounded-xl border @error('placa') border-red-500 @else border-gray-300 @enderror
                            dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none" />
                    @error('placa') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Modelo -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Modelo *</label>
                    <input wire:model.lazy="modelo" type="text" placeholder="Ej: NHR, Hilux, F-150"
                        class="w-full px-4 py-2 rounded-xl border @error('modelo') border-red-500 @else border-gray-300 @enderror
                            dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none" />
                    @error('modelo') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Marca -->
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">Marca</label>
                    <input wire:model.lazy="marca" type="text" placeholder="Toyota, Chevrolet..."
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-violet-600 focus:outline-none" />
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="flex items-center gap-2 px-6 py-3 bg-violet-600 hover:bg-violet-700 text-white text-base font-bold rounded-2xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-save"></i> {{ $isEdit ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </section>
    </form>

    <!-- Tabla de vehículos -->
    <section class="space-y-6 p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
        <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Vehículos Registrados</h3>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm bg-white dark:bg-gray-800 rounded-xl overflow-hidden">
                <thead class="bg-violet-600 text-white">
                    <tr>
                        <th class="p-3 text-left font-semibold">ID</th>
                        <th class="p-3 text-left font-semibold">Placa</th>
                        <th class="p-3 text-left font-semibold">Modelo</th>
                        <th class="p-3 text-left font-semibold">Marca</th>
                        <th class="p-3 text-center font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($vehiculos as $veh)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="p-3">{{ $veh->id }}</td>
                            <td class="p-3">{{ $veh->placa }}</td>
                            <td class="p-3">{{ $veh->modelo }}</td>
                            <td class="p-3">{{ $veh->marca }}</td>
                            <td class="p-3 text-center space-x-2">
                                <button wire:click="editar({{ $veh->id }})" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="eliminar({{ $veh->id }})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400 italic">No hay vehículos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.addEventListener('vehiculo-guardado', event => {
        Swal.fire({
            icon: 'success',
            title: '¡Guardado!',
            text: event.detail.mensaje,
            timer: 2000,
            showConfirmButton: false
        });
    });

    window.addEventListener('vehiculo-eliminado', event => {
        Swal.fire({
            icon: 'success',
            title: '¡Eliminado!',
            text: event.detail.mensaje,
            timer: 2000,
            showConfirmButton: false
        });
    });
</script>
