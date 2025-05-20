<div class="p-10 bg-gradient-to-br from-violet-200 via-white to-purple-100 dark:from-gray-900 dark:via-gray-800 dark:to-black rounded-3xl shadow-2xl space-y-12">

    {{-- Encabezado --}}
<header class="relative text-center py-4 px-4 bg-gradient-to-r from-gray-100 to-gray-300 rounded-3xl shadow-md">
    <div class="flex flex-col items-center space-y-1">
        <div class="bg-gray-800 text-white p-2 rounded-full shadow">
            <i class="fas fa-truck-loading text-xl animate-bounce"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 tracking-wide">
            Mis Rutas Asignadas
        </h2>
        <p class="text-xs text-gray-600 tracking-wide">
            {{ \Carbon\Carbon::now('America/Bogota')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
        </p>
    </div>
    <div class="absolute top-0 left-0 w-2 h-full bg-white rounded-l-3xl opacity-10"></div>
    <div class="absolute bottom-0 right-0 w-2 h-full bg-white rounded-r-3xl opacity-10"></div>
</header>




    @if($rutas->isEmpty())
        <div class="text-center text-lg text-gray-500 dark:text-gray-400 italic mt-8 animate-fade-in">
            💤 No tienes rutas asignadas para hoy.
        </div>
    @else
        {{-- Tarjetas --}}
        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-10 animate-fade-in-up">
            @foreach($rutas as $ruta)
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-3xl shadow-2xl p-6 hover:shadow-violet-500/30 transform transition duration-300 hover:-translate-y-1 hover:scale-[1.02]">

                    {{-- Cabecera --}}
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center space-x-4">
                            <div class="bg-violet-300 dark:bg-violet-800 p-3 rounded-full shadow-md ring-2 ring-violet-500/30">
                                <i class="fas fa-truck text-violet-700 dark:text-white text-2xl animate-pulse"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Ruta</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Vehículo: <span class="font-semibold">{{ $ruta->vehiculo->placa ?? '-' }}</span>
                                </p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($ruta->fecha_salida)->format('d/m/Y') }}
                        </span>
                    </div>

                    {{-- Información de ruta --}}
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Ruta asignada:</p>
                            <p class="text-base text-gray-800 dark:text-gray-200">{{ $ruta->ruta }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Conductores:</p>
                            @if($ruta->conductores->count())
                                <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300 mt-1">
                                    @foreach($ruta->conductores as $conductor)
                                        <li>{{ $conductor->name }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="italic text-gray-400">Sin conductores asignados</p>
                            @endif
                        </div>
                    </div>

                    {{-- Botón --}}
                    <div class="mt-6 text-right">
                        <button wire:click="verInventario({{ $ruta->id }})"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 px-4 py-2 rounded-xl shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-400">
                            <i class="fas fa-box-open"></i> Ver Inventario
                        </button>
                    </div>

                    {{-- Inventario --}}
                    @if($rutaVistaId === $ruta->id)
                        <div class="mt-6 p-5 bg-gray-100 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-3">
                            <h4 class="text-md font-bold text-gray-800 dark:text-white mb-2">📦 Inventario Asignado</h4>
                            <ul class="space-y-2 text-sm">
                                @forelse($inventarioVista as $item)
                                    <li class="flex justify-between items-center px-3 py-2 bg-white dark:bg-gray-900 rounded-md shadow-sm border dark:border-gray-800">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $item['producto'] }}</span>
                                        <span class="text-gray-500 dark:text-gray-400 text-sm">{{ $item['bodega'] }}</span>
                                        <span class="font-bold text-violet-600 dark:text-violet-400 text-sm">{{ $item['cantidad'] }}</span>
                                    </li>
                                @empty
                                    <li class="text-center text-gray-400 italic">Sin inventario asignado</li>
                                @endforelse
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    @endif
</div>
