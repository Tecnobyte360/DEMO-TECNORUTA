@assets
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js" defer></script>
@endassets

<div
    x-data="{ showCreateModal:false, showEditModal:false, showImportModal:false, showPedidosModal:false, tab:'list' }"
    x-init="window.addEventListener('abrir-modal-pedidos', () => { showPedidosModal = true; tab = 'list' })"
    wire:init="loadListas"
    class="space-y-6"
>
    <!-- Header + filtros -->
    <section class="rounded-3xl border border-gray-200 dark:border-gray-800 bg-white/70 dark:bg-gray-900/70 backdrop-blur">
        <header class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-600/10 text-violet-600">
                    <i class="fas fa-users"></i>
                </span>
                <div>
                    <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">Socios de Negocio</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Clientes a la izquierda · Proveedores a la derecha</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="showCreateModal=true" class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white shadow-sm">
                    <i class="fas fa-plus mr-2"></i> Nuevo socio
                </button>
                <button @click="showImportModal=true" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <i class="fas fa-file-import mr-2"></i> Importar
                </button>
            </div>
        </header>

        <div class="px-6 py-5 grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            <!-- selector puntual -->
            <div class="lg:col-span-6">
                <label class="block text-xs text-gray-500 mb-1">Ir a un socio puntual</label>
                <div wire:ignore x-data x-init="
                    const t = new TomSelect($refs.selSocio,{
                        placeholder:'Selecciona un socio…',
                        allowEmptyOption:true, create:false,
                        onChange:v => @this.set('socioNegocioId', v)
                    });
                    Livewire.hook('message.processed',()=>t.refreshOptions(false));
                ">
                    <select x-ref="selSocio" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">— Todos —</option>
                        @foreach($clientesFiltrados as $c)
                            <option value="{{ $c['id'] }}">{{ $c['razon_social'] }} ({{ $c['nit'] }})</option>
                        @endforeach
                    </select>
                </div>
                @if($socioNegocioId || $buscador)
                    <div class="mt-2">
                        <button type="button"
                                wire:click="$set('socioNegocioId', null); $set('buscador',''); loadListas();"
                                class="text-xs px-3 py-1 rounded-full border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">
                            Limpiar filtros
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- DOS COLUMNAS -->
    <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- CLIENTES -->
        <div class="rounded-3xl border border-gray-200 dark:border-gray-800 bg-white/70 dark:bg-gray-900/70 overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600/10 text-emerald-600">
                        <i class="fas fa-handshake"></i>
                    </span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Clientes</h3>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                    {{ count($clientes) }}
                </span>
            </header>

            <div class="p-4 md:p-6">
                <!-- móvil cards -->
                <div class="md:hidden space-y-3">
                    @forelse($clientes as $s)
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-base font-bold">{{ ucwords(strtolower($s->razon_social)) }}</div>
                                    <div class="text-xs text-gray-500">NIT: {{ $s->nit ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ ucwords(strtolower($s->direccion ?? '')) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[11px] text-gray-500">Saldo</div>
                                    <div class="text-sm font-extrabold text-emerald-600">
                                        ${{ number_format($s->saldoPendiente ?? 0, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-end gap-3">
                                <button wire:click="editsocio({{ $s->id }})" @click="showEditModal=true" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-pen-to-square mr-1"></i> Editar
                                </button>
                                <button wire:click="mostrarPedidos({{ $s->id }})" @click="showPedidosModal=true" class="text-violet-600 hover:text-violet-800 text-sm">
                                    <i class="fas fa-file-invoice mr-1"></i> Pedidos
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">Sin resultados</div>
                    @endforelse
                </div>

                <!-- desktop tabla -->
                <div class="hidden md:block overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 uppercase text-xs tracking-wider">
                            <tr>
                                <th class="p-3 text-left">ID</th>
                                <th class="p-3 text-left">Razón Social</th>
                                <th class="p-3 text-left">NIT</th>
                                <th class="p-3 text-left">Dirección</th>
                                <th class="p-3 text-center">Saldo</th>
                                <th class="p-3 text-center">Pedidos</th>
                                <th class="p-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($clientes as $socio)
                                <tr class="hover:bg-violet-50 dark:hover:bg-gray-800 transition">
                                    <td class="p-3">{{ $socio->id }}</td>
                                    <td class="p-3">{{ ucwords(strtolower($socio->razon_social)) }}</td>
                                    <td class="p-3">{{ $socio->nit }}</td>
                                    <td class="p-3">{{ ucwords(strtolower($socio->direccion ?? '')) }}</td>
                                    <td class="p-3 text-center font-bold text-emerald-600">
                                        ${{ number_format($socio->saldoPendiente ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <button wire:click="mostrarPedidos({{ $socio->id }})" @click="showPedidosModal=true"
                                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            <i class="fas fa-file-invoice"></i> Ver
                                        </button>
                                    </td>
                                    <td class="p-3 text-center space-x-2">
                                        <button wire:click="editsocio({{ $socio->id }})" @click="showEditModal=true" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="delete({{ $socio->id }})" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-6 text-center text-gray-500 dark:text-gray-400">Sin resultados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- PROVEEDORES -->
        <div class="rounded-3xl border border-gray-200 dark:border-gray-800 bg-white/70 dark:bg-gray-900/70 overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-600/10 text-sky-600">
                        <i class="fas fa-truck-field"></i>
                    </span>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Proveedores</h3>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
                    {{ count($proveedores) }}
                </span>
            </header>

            <div class="p-4 md:p-6">
                <!-- móvil cards -->
                <div class="md:hidden space-y-3">
                    @forelse($proveedores as $s)
                        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-base font-bold">{{ ucwords(strtolower($s->razon_social)) }}</div>
                                    <div class="text-xs text-gray-500">NIT: {{ $s->nit ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ ucwords(strtolower($s->direccion ?? '')) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[11px] text-gray-500">Saldo</div>
                                    <div class="text-sm font-extrabold text-sky-600">
                                        ${{ number_format($s->saldoPendiente ?? 0, 2, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-end gap-3">
                                <button wire:click="editsocio({{ $s->id }})" @click="showEditModal=true" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-pen-to-square mr-1"></i> Editar
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed p-6 text-center text-gray-500">Sin resultados</div>
                    @endforelse
                </div>

                <!-- desktop tabla -->
                <div class="hidden md:block overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 uppercase text-xs tracking-wider">
                            <tr>
                                <th class="p-3 text-left">ID</th>
                                <th class="p-3 text-left">Razón Social</th>
                                <th class="p-3 text-left">NIT</th>
                                <th class="p-3 text-left">Dirección</th>
                                <th class="p-3 text-center">Saldo</th>
                                <th class="p-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($proveedores as $socio)
                                <tr class="hover:bg-violet-50 dark:hover:bg-gray-800 transition">
                                    <td class="p-3">{{ $socio->id }}</td>
                                    <td class="p-3">{{ ucwords(strtolower($socio->razon_social)) }}</td>
                                    <td class="p-3">{{ $socio->nit }}</td>
                                    <td class="p-3">{{ ucwords(strtolower($socio->direccion ?? '')) }}</td>
                                    <td class="p-3 text-center font-bold text-sky-600">
                                        ${{ number_format($socio->saldoPendiente ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="p-3 text-center space-x-2">
                                        <button wire:click="editsocio({{ $socio->id }})" @click="showEditModal=true" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="delete({{ $socio->id }})" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-6 text-center text-gray-500 dark:text-gray-400">Sin resultados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Modales (igual que antes) ===== --}}
    <!-- Crear -->
    <div class="fixed inset-0 z-50 grid place-items-center bg-black/50 backdrop-blur-sm" x-show="showCreateModal" x-cloak @keydown.escape.window="showCreateModal=false">
        <div class="w-full max-w-4xl mx-4 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl overflow-auto max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-bold">Crear socio</h3>
                <button class="text-red-600" @click="showCreateModal=false"><i class="fas fa-times-circle"></i></button>
            </div>
            <div class="p-6">@livewire('socio-negocio.create')</div>
        </div>
    </div>

    <!-- Importar -->
    <div class="fixed inset-0 z-50 grid place-items-center bg-black/50 backdrop-blur-sm" x-show="showImportModal" x-cloak @keydown.escape.window="showImportModal=false">
        <div class="w-full max-w-3xl mx-4 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl overflow-auto max-h-[85vh]">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-bold">Importar socios de negocio</h3>
                <button class="text-red-600" @click="showImportModal=false"><i class="fas fa-times-circle"></i></button>
            </div>
            <div class="p-6">@livewire('socio-negocio.importar-excel')</div>
        </div>
    </div>

    <!-- Editar -->
    <div class="fixed inset-0 z-50 grid place-items-center bg-black/50 backdrop-blur-sm" x-show="showEditModal" x-cloak @keydown.escape.window="showEditModal=false">
        <div class="w-full max-w-4xl mx-4 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl overflow-auto max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-bold">Editar socio</h3>
                <button class="text-red-600" @click="showEditModal=false"><i class="fas fa-times-circle"></i></button>
            </div>
            <div class="p-6">@livewire('socio-negocio.edit')</div>
        </div>
    </div>

    <!-- Pedidos -->
    <div class="fixed inset-0 z-50 grid place-items-center bg-black/60 backdrop-blur-sm"
         x-show="showPedidosModal" x-cloak
         @keydown.escape.window="showPedidosModal=false; @this.cerrarPedidosModal()"
         @click.self="showPedidosModal=false; @this.cerrarPedidosModal()">
        <div class="w-full max-w-4xl mx-4 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl overflow-auto max-h-[85vh]">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-violet-500"></i> Pedidos del socio
                </h3>
                <button class="text-red-600" @click="showPedidosModal=false; @this.cerrarPedidosModal()">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>

            <div class="p-6">
                <div x-show="tab==='list'">
                    @if(empty($pedidosSocio))
                        <p class="text-gray-500 dark:text-gray-400 italic">No hay pedidos a crédito con saldo pendiente.</p>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 uppercase text-xs tracking-wider">
                                    <tr>
                                        <th class="px-4 py-2">ID</th>
                                        <th class="px-4 py-2">Fecha</th>
                                        <th class="px-4 py-2">Ruta</th>
                                        <th class="px-4 py-2">Conductor</th>
                                        <th class="px-4 py-2 text-right">Total pendiente</th>
                                        <th class="px-4 py-2 text-center">Tipo de pago</th>
                                        <th class="px-4 py-2 text-center">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($pedidosSocio as $p)
                                        <tr>
                                            <td class="px-4 py-2">{{ $p['id'] }}</td>
                                            <td class="px-4 py-2">{{ $p['fecha'] }}</td>
                                            <td class="px-4 py-2">{{ $p['ruta'] }}</td>
                                            <td class="px-4 py-2">{{ $p['usuario'] }}</td>
                                            <td class="px-4 py-2 text-right font-semibold dark:text-emerald-400">${{ $p['total'] }}</td>
                                            <td class="px-4 py-2 text-center capitalize">{{ $p['tipo_pago'] }}</td>
                                            <td class="px-4 py-2 text-center">
                                                <button wire:click="mostrarDetallePedido({{ $p['id'] }})" @click="tab='detail'"
                                                        class="text-xs px-2 py-1 rounded-lg bg-amber-100 text-amber-700 hover:bg-amber-200">
                                                    Ver detalle
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="px-4 py-2 text-right text-sm">
                                            <b>Total general crédito:</b>
                                            <span class="text-emerald-600 dark:text-emerald-400">
                                                ${{ number_format(collect($pedidosSocio)->sum('total_raw'),2,',','.') }}
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>

                <div x-show="tab==='detail'" x-cloak class="space-y-4">
                    @if(!$mostrarDetalleModal)
                        <p class="text-gray-500 dark:text-gray-400 italic">Selecciona un pedido para ver el detalle.</p>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 uppercase text-xs tracking-wider">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Producto</th>
                                        <th class="px-3 py-2 text-center">Cantidad</th>
                                        <th class="px-3 py-2 text-right">Precio</th>
                                        <th class="px-3 py-2 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($detallesPedido as $d)
                                        <tr>
                                            <td class="px-3 py-2">{{ $d['producto'] }}</td>
                                            <td class="px-3 py-2 text-center">{{ $d['cantidad'] }}</td>
                                            <td class="px-3 py-2 text-right">${{ $d['precio_unitario'] }}</td>
                                            <td class="px-3 py-2 text-right">${{ $d['subtotal'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="px-3 py-2 text-right font-bold">
                                            Total: $
                                            {{ number_format(collect($detallesPedido)->sum(fn($x)=> floatval(str_replace(['.',',','$'],['','.',''],$x['subtotal']))), 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="flex justify-between">
                            <button class="px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-700" @click="tab='list'">← Volver</button>
                            <button class="px-3 py-2 rounded-xl bg-violet-600 text-white hover:bg-violet-700"
                                    @click="showPedidosModal=false; @this.cerrarPedidosModal()">Cerrar</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
