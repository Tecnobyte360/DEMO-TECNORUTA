@assets
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
@endassets

<div 
   x-data="{ 
    showCreateModal: false, 
    showEditModal: false, 
    showImportModal: false,
    showPedidosModal: false 
}"

    x-init="console.log('Alpine.js cargado')"
    wire:init="loadSocios"
    class="p-6 bg-white dark:bg-gray-900 shadow-md rounded-lg transition-all duration-300"
>

    <!-- Header -->
    <div class="flex items-center justify-between mb-6 border-b pb-3">
        <h2 class="text-xl sm:text-2xl font-semibold text-gray-700 dark:text-gray-200 flex items-center space-x-2">
            <i class="fas fa-users-cog text-indigo-500"></i>
            <span>Gestión de Socios de Negocio</span>
        </h2>

        <div class="flex space-x-4">
        
            <button 
                @click="showCreateModal = true" 
                class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition flex items-center space-x-2"
            >
                <i class="fas fa-plus"></i>
                <span>Nuevo Socio</span>
            </button>

            <button 
                @click="showImportModal = true" 
                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition flex items-center space-x-2"
            >
                <i class="fas fa-users"></i>
                <span>Importar socios de negocio</span>
            </button>
            
        </div>
    </div>

    <!-- Tabla Socios -->
  <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-md">
  <div class="mb-4 max-w-md">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filtrar por cliente</label>
    <div wire:ignore x-data x-init="
        tom = new TomSelect($refs.select, {
            placeholder: 'Selecciona un cliente...',
            allowEmptyOption: true,
            create: false,
            onChange: value => @this.set('socioNegocioId', value),
        });
        Livewire.hook('message.processed', () => tom.refreshOptions(false));
    ">
        <select x-ref="select" class="w-full rounded-md dark:bg-gray-800 dark:text-white">
            <option value="">-- Selecciona un cliente --</option>
            @foreach($clientesFiltrados as $cliente)
                <option value="{{ $cliente['id'] }}">
                    {{ $cliente['razon_social'] }} ({{ $cliente['nit'] }})
                </option>
            @endforeach
        </select>
    </div>
</div>

    <table class="min-w-full text-sm sm:text-base bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl">
        <thead class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white text-xs uppercase tracking-wider">
            <tr>
                <th class="px-4 py-3 text-left"><i class="fas fa-hashtag mr-1"></i> ID</th>
                <th class="px-4 py-3 text-left"><i class="fas fa-building mr-1"></i> Razón Social</th>
                <th class="px-4 py-3 text-left"><i class="fas fa-id-card mr-1"></i> NIT</th>
                <th class="px-4 py-3 text-left"><i class="fas fa-map-marker-alt mr-1"></i> Dirección</th>
                <th class="px-4 py-3 text-center"><i class="fas fa-wallet mr-1"></i> Saldo Pendiente de pago</th>
                <th class="px-4 py-3 text-center"><i class="fas fa-file-invoice mr-1"></i> Pedidos</th>
                <th class="px-4 py-3 text-center"><i class="fas fa-cogs mr-1"></i> Acciones</th>
            </tr>
        </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach ($socioNegocios as $socio)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200" wire:key='{{ $socio->id }}'>
                                <td class="px-4 py-2">{{ $socio->id }}</td>
                                <td class="px-4 py-2">{{ ucwords(strtolower($socio->razon_social)) }}</td>
                                <td class="px-4 py-2">{{ ucwords(strtolower($socio->nit)) }}</td>
                                <td class="px-4 py-2">{{ ucwords(strtolower($socio->direccion)) }}</td>
                              <td class="px-4 py-2 text-center text-green-700 font-semibold">
    ${{ number_format(
        collect($socio->pedidos)
            ->where('tipo_pago', 'credito')
            ->map(function($pedido) {
                $total = $pedido->detalles->sum(fn($d) => $d->cantidad * floatval($d->precio_aplicado ?? $d->precio_unitario));
                $pagado = $pedido->pagos->sum('monto');
                return $total > $pagado ? $total - $pagado : 0;
            })->sum(),
        2, ',', '.')
    }}
</td>




                    <td class="px-4 py-2 text-center">
                        <button 
                            wire:click="mostrarPedidos({{ $socio->id }})" 
                            @click="showPedidosModal = true"
                            class="inline-flex items-center text-violet-600 hover:text-violet-800 font-medium transition-all"
                            title="Ver pedidos del socio">
                            <i class="fas fa-file-invoice mr-1"></i> Pedidos
                        </button>
                    </td>
                    <td class="px-4 py-2 text-center space-x-2">
                        <button 
                            wire:click="editsocio({{ $socio->id }})" 
                            @click="showEditModal = true" 
                            wire:loading.attr="disabled" 
                            class="text-blue-500 hover:text-blue-700 transition"
                            title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button 
                            wire:click="delete({{ $socio->id }})" 
                            class="text-red-500 hover:text-red-700 transition p-2 rounded-lg"
                            title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>


    <!-- Modal Crear Socio -->
    <div 
        class="fixed inset-0 flex items-center justify-center bg-opacity-50 backdrop-blur-sm z-50"
        x-show="showCreateModal" 
        x-cloak
        @keydown.escape.window="showCreateModal = false"
        @close-modal.window="showCreateModal = false"
        wire:ignore.self
    >
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Crear Nuevo Socio</h3>
                <button 
                    @click="showCreateModal = false" 
                    class="text-gray-600 dark:text-gray-400 hover:text-red-500"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>

            @livewire('socio-negocio.create')
        </div>
    </div>

    <!-- Modal Importar Excel -->
    <div
        class="fixed inset-0 flex items-center justify-center bg-opacity-50 backdrop-blur-sm z-50"
        x-show="showImportModal" 
        x-cloak
        @keydown.escape.window="showImportModal = false"
        @close-modal.window="showImportModal = false"
        wire:ignore.self
    >
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Importar Socios de Negocio</h3>
                <button 
                    @click="showImportModal = false" 
                    class="text-gray-600 dark:text-gray-400 hover:text-red-500"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>

            @livewire('socio-negocio.importar-excel')
        </div>
    </div>

    <!-- Modal Editar Socio -->
    <div
        class="fixed inset-0 flex items-center justify-center bg-opacity-50 backdrop-blur-sm z-50"
        x-show="showEditModal" 
        x-cloak
        @keydown.escape.window="showEditModal = false"
        @close-modal.window="showEditModal = false"
        wire:ignore.self
    >
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Editar Socio</h3>
                <button 
                    @click="showEditModal = false" 
                    class="text-gray-600 dark:text-gray-400 hover:text-red-500"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>

            @livewire('socio-negocio.edit')
        </div>
    </div>

   <!-- Modal Mostrar Pedidos -->
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm transition-all duration-300"
        x-show="showPedidosModal"
        x-cloak
        @keydown.escape.window="showPedidosModal = false; @this.cerrarPedidosModal()"
        @click.self="showPedidosModal = false; @this.cerrarPedidosModal()"
    >
        <div class="w-full max-w-3xl mx-4 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-6 animate-fade-in-up space-y-6 relative overflow-auto max-h-[80vh]">

            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3">
                <h3 class="text-xl font-extrabold text-gray-800 dark:text-white tracking-tight flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-violet-500 dark:text-violet-400 text-lg"></i>
                    Pedidos del Socio
                </h3>
                <button @click="showPedidosModal = false; @this.cerrarPedidosModal()" 
                        class="text-gray-400 hover:text-red-500 transition-all text-xl focus:outline-none">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
    <div x-show="tab==='list'" class="mt-4">
                    @if(empty($pedidosSocio))
                        <p class="text-gray-500 dark:text-gray-400 italic">No hay pedidos registrados para este socio.</p>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700 shadow-md">
                            <table class="w-full text-left text-sm border-collapse bg-gradient-to-br from-gray-100 to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl overflow-hidden">
                                <thead class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white uppercase text-xs tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3">ID</th>
                                    
                                        <th class="px-6 py-3">Fecha</th>
                                        <th class="px-6 py-3">Ruta</th>
                                        <th class="px-6 py-3">Conductor</th>
                                        <th class="px-6 py-3 text-right">Total pedido</th>
                                            <th class="px-6 py-3 text-center">Tipo de pago</th>
                                        <th class="px-6 py-3 text-center">Ver Detalle</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                    @foreach($pedidosSocio as $pedido)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4">{{ $pedido['id'] }}</td>
                                        
                                            <td class="px-6 py-4">{{ $pedido['fecha'] }}</td>
                                            <td class="px-6 py-4">{{ $pedido['ruta'] }}</td>
                                            <td class="px-6 py-4">{{ $pedido['usuario'] }}</td>
                                            <td class="px-6 py-4 text-right font-semibold  dark:text-green-400">
                                                    $ {{ $pedido['total'] }}
                                                </td>
<td class="px-6 py-4 text-center capitalize">{{ $pedido['tipo_pago'] }}</td>

                                            <td class="px-6 py-4 text-center">
                                               <button
                                                wire:click="mostrarDetallePedido({{ $pedido['id'] }})"
                                                @click="$dispatch('detalle-cargado')"
                                                class="text-xs px-2 py-1 rounded-lg bg-yellow-100 text-yellow-700 hover:bg-yellow-200"
                                            >
                                                Ver Detalle
                                            </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            <!-- Detalle de Pedido -->
            <div x-show="tab==='detail'" class="mt-4">
                @if(!$mostrarDetalleModal)
                    <p class="text-gray-500 dark:text-gray-400 italic">Seleccione un pedido para ver su detalle.</p>
                    <div class="flex justify-end px-4 py-3 bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-2xl">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">
                            Total general de creditos:
                            <span class="text-green-600 dark:text-green-400">
                                ${{ number_format(collect($pedidosSocio)->sum('total_raw'), 2, ',', '.') }}
                            </span>
                        </span>
                    </div>

                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-xs">
                        <table class="min-w-full text-left table-auto border-collapse">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700">
                                    <th class="p-2 border dark:border-gray-600">Producto</th>
                                    <th class="p-2 border dark:border-gray-600 text-center">Cantidad</th>
                                    <th class="p-2 border dark:border-gray-600 text-right">Precio Unitario</th>
                                    <th class="p-2 border dark:border-gray-600 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detallesPedido as $item)
                                    <tr class="hover:bg-gray-200 dark:hover:bg-gray-700">
                                        <td class="p-2 border dark:border-gray-600">{{ $item['producto'] }}</td>
                                        <td class="p-2 border dark:border-gray-600 text-center">{{ $item['cantidad'] }}</td>
                                        <td class="p-2 border dark:border-gray-600 text-right">${{ $item['precio_unitario'] }}</td>
                                        <td class="p-2 border dark:border-gray-600 text-right">${{ $item['subtotal'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-end pt-2">
                        <div class="font-bold text-base">
                            Total: ${{ number_format(collect($detallesPedido)->sum(fn($d)=> floatval(str_replace(['.', '$'], ['',''], $d['subtotal']))), 2, ',', '.') }}
                        </div>
                    </div>
                @endif
            </div>
       
</div>

     

    </div>
    
</div>


</div>

</div>

</div>


</div>
