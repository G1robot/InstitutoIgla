<div class="px-4 pb-8">

    {{-- ========================================== --}}
    {{-- TODO ESTO SE OCULTA AL IMPRIMIR            --}}
    {{-- ========================================== --}}
    <div class="ocultar-al-imprimir">
        
        <div class="mb-8">
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">PUNTO DE VENTA (INSUMOS)</h2>
            <p class="text-sm text-gray-500 mt-1">Registra la venta de artículos, uniformes y servicios adicionales.</p>
        </div>

        {{-- LAYOUT PRINCIPAL --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- COLUMNA IZQUIERDA: CATÁLOGO --}}
            <div class="lg:col-span-2 flex flex-col gap-5">
                
                {{-- 1. Buscador de Estudiante (UX Infalible) --}}
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative z-20">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Cliente / Estudiante *</label>
                    
                    @if($estudianteSeleccionado)
                        {{-- Vista de Éxito --}}
                        <div class="w-full flex items-center justify-between border-2 border-green-500 bg-green-50 rounded-lg p-2.5 transition-all animate-fade-in-down">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-circle-check text-green-500 text-lg bg-white rounded-full"></i>
                                <div>
                                    <span class="font-bold text-green-800">{{ $estudianteSeleccionado->nombre }} {{ $estudianteSeleccionado->apellido }}</span>
                                    <span class="text-green-600 text-xs block">CI: {{ $estudianteSeleccionado->ci }}</span>
                                </div>
                            </div>
                            <button wire:click="$set('estudianteSeleccionado', null)" class="text-red-400 hover:text-red-600 transition bg-white rounded-full px-3 py-1 shadow-sm text-xs font-bold" title="Cambiar estudiante">
                                <i class="fa-solid fa-xmark mr-1"></i> Cambiar
                            </button>
                        </div>
                    @else
                        {{-- Vista de Búsqueda --}}
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-user-magnifying-glass text-gray-400"></i>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="searchEstudiante" 
                                class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 focus:bg-white" 
                                placeholder="Escribe el nombre o CI para buscar...">
                        </div>
                        
                        @if(!empty($estudiantesEncontrados))
                            <ul class="absolute w-full left-0 bg-white shadow-2xl border border-orange-200 mt-1 rounded-lg overflow-hidden z-50 animate-fade-in-down divide-y divide-gray-100">
                                @foreach($estudiantesEncontrados as $est)
                                    <li wire:click="seleccionarEstudiante({{ $est->id_estudiante }})" 
                                        class="p-3 hover:bg-orange-50 cursor-pointer flex items-center gap-3 transition">
                                        <i class="fa-solid fa-chevron-right text-orange-400 text-xs"></i>
                                        <div>
                                            <span class="font-bold text-gray-800">{{ $est->nombre }} {{ $est->apellido }}</span>
                                            <span class="text-gray-500 text-xs block">CI: {{ $est->ci }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endif
                    @error('general') <span class="text-red-500 text-xs mt-2 block"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> @enderror
                </div>

                {{-- 2. Filtros de Categoría --}}
                <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                    <button wire:click="filtrarCategoria(null)" 
                        class="px-4 py-1.5 rounded-full text-sm font-bold shadow-sm transition-all whitespace-nowrap {{ is_null($id_categoria) ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        Todos
                    </button>
                    @foreach($categorias as $cat)
                        <button wire:click="filtrarCategoria({{ $cat->id_categoria_articulo }})" 
                            class="px-4 py-1.5 rounded-full text-sm font-bold shadow-sm transition-all whitespace-nowrap
                            {{ $id_categoria == $cat->id_categoria_articulo ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-600 border border-gray-200 hover:bg-orange-50 hover:text-orange-600' }}">
                            {{ $cat->nombre }}
                        </button>
                    @endforeach
                </div>

                {{-- 3. Grid de Artículos --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @forelse($articulos as $art)
                        <div wire:key="art-{{ $art->id_articulo }}" 
                             class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-orange-300 hover:shadow-md transition-all flex flex-col justify-between h-full group">
                            
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">{{ $art->categoria->nombre ?? 'General' }}</span>
                                <h5 class="font-bold text-gray-800 leading-tight mb-2 group-hover:text-orange-600 transition-colors">{{ $art->nombre }}</h5>
                            </div>

                            <div class="mt-4 flex justify-between items-end">
                                <div>
                                    <span class="block text-lg font-black text-gray-800">{{ number_format($art->precio, 2) }} <span class="text-xs text-gray-400">Bs</span></span>
                                    
                                    @if(is_null($art->stock))
                                        <span class="text-[10px] font-bold text-blue-500 bg-blue-50 px-1.5 rounded"><i class="fa-solid fa-infinity"></i> Servicio</span>
                                    @else
                                        <span class="text-[10px] font-bold px-1.5 rounded {{ $art->stock < 5 ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-600' }}">
                                            Stock: {{ $art->stock }}
                                        </span>
                                    @endif
                                </div>
                                <button wire:click="agregarAlCarrito({{ $art->id_articulo }})" 
                                    class="bg-orange-100 text-orange-600 hover:bg-orange-500 hover:text-white w-10 h-10 rounded-full flex items-center justify-center transition-colors">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 sm:col-span-3 text-center py-12 text-gray-400 bg-white rounded-xl border border-dashed border-gray-200">
                            <i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300"></i>
                            <p>No hay artículos en esta categoría.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- COLUMNA DERECHA: CARRITO Y PAGO --}}
            <div class="lg:col-span-1">
                
                {{-- Botón Historial --}}
                <div class="mb-5">
                    <button wire:click="abrirHistorial" class="w-full bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 hover:text-orange-600 transition flex items-center justify-center gap-2 font-bold text-sm">
                        <i class="fas fa-history"></i> Ver Ventas de Hoy
                    </button>
                </div>

                {{-- Carrito Sticky --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 sticky top-4 overflow-hidden flex flex-col max-h-[calc(100vh-100px)]">
                    
                    {{-- Encabezado Carrito --}}
                    <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
                        <span class="font-bold tracking-wide"><i class="fas fa-shopping-cart text-orange-400 mr-2"></i> CARRITO</span>
                        <span class="bg-gray-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ count($carrito) }} items</span>
                    </div>

                    {{-- Lista de Items --}}
                    <div class="p-4 space-y-3 overflow-y-auto flex-1 custom-scrollbar">
                        @forelse($carrito as $id => $item)
                            <div class="flex justify-between items-center bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                <div class="flex-1 pr-2">
                                    <p class="text-xs font-bold text-gray-800 leading-tight mb-1">{{ $item['nombre'] }}</p>
                                    <p class="text-[10px] text-gray-500 font-mono">{{ number_format($item['precio'], 2) }} Bs. x {{ $item['cantidad'] }}</p>
                                </div>
                                <div class="flex items-center gap-1 bg-white rounded-md border border-gray-200 p-0.5">
                                    <button wire:click="restarDelCarrito({{ $id }})" class="text-gray-500 hover:text-red-500 hover:bg-red-50 w-6 h-6 rounded flex items-center justify-center transition"><i class="fa-solid fa-minus text-[10px]"></i></button>
                                    <span class="text-xs font-bold w-5 text-center">{{ $item['cantidad'] }}</span>
                                    <button wire:click="agregarAlCarrito({{ $id }})" class="text-gray-500 hover:text-green-500 hover:bg-green-50 w-6 h-6 rounded flex items-center justify-center transition"><i class="fa-solid fa-plus text-[10px]"></i></button>
                                </div>
                                <div class="text-right w-16 ml-2">
                                    <span class="block font-black text-sm text-gray-800">{{ number_format($item['subtotal'], 2) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-400 py-10">
                                <i class="fa-solid fa-basket-shopping text-3xl mb-3 text-gray-200"></i>
                                <p class="text-sm">El carrito está vacío</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Sección de Pago --}}
                    <div class="p-5 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between items-end mb-5">
                            <span class="text-gray-500 font-bold text-sm uppercase tracking-wider">Total a Pagar</span>
                            <span class="text-3xl font-black text-orange-600 leading-none">{{ number_format($total, 2) }} <span class="text-sm text-orange-400">Bs</span></span>
                        </div>

                        @if($total > 0)
                            <div class="space-y-3 mb-5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Método de Pago</label>
                                
                                @foreach($metodosPago as $metodo)
                                    <div class="flex shadow-sm rounded-lg overflow-hidden border border-gray-300 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 transition-all">
                                        <span class="inline-flex items-center justify-center px-3 bg-gray-100 text-gray-600 text-xs font-bold w-24 border-r border-gray-300">
                                            {{ $metodo->nombre }}
                                        </span>
                                        <input type="number" step="0.50" wire:model.live="montosPago.{{ $metodo->id_metodo_pago }}" 
                                            class="flex-1 w-full px-3 py-2 border-none text-sm font-bold text-gray-800 focus:ring-0 bg-white" 
                                            placeholder="0.00">
                                        <button wire:click="llenarSaldo({{ $metodo->id_metodo_pago }})" class="px-3 bg-white text-gray-400 hover:text-orange-500 transition border-l border-gray-200" title="Autocompletar saldo restante">
                                            <i class="fa-solid fa-reply"></i>
                                        </button>
                                    </div>
                                @endforeach
                                
                                <div class="flex justify-between text-xs pt-2 border-t border-gray-200 mt-2">
                                    <span>Ingresado: <strong class="{{ $totalIngresado >= $total - 0.1 ? 'text-green-600' : 'text-red-500' }} font-mono">{{ number_format($totalIngresado, 2) }}</strong></span>
                                    <span>Cambio: <strong class="font-mono">{{ number_format(max(0, $totalIngresado - $total), 2) }}</strong></span>
                                </div>
                                @error('pago') <span class="text-red-500 text-xs block font-bold"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> @enderror
                            </div>

                            <button wire:click="realizarVenta" 
                                wire:loading.attr="disabled"
                                class="w-full bg-gray-800 text-white py-3.5 rounded-xl font-bold shadow-lg hover:bg-black transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ $totalIngresado < $total - 0.1 ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="realizarVenta"><i class="fa-solid fa-cash-register"></i> Procesar Venta</span>
                                <span wire:loading wire:target="realizarVenta"><i class="fa-solid fa-spinner fa-spin"></i> Procesando...</span>
                            </button>
                        @else
                            <button disabled class="w-full bg-gray-200 text-gray-400 py-3.5 rounded-xl font-bold cursor-not-allowed flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cash-register"></i> Procesar Venta
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div> {{-- Fin del div ocultar-al-imprimir --}}

    {{-- ========================================== --}}
    {{-- MODAL DE ÉXITO (MODO PANTALLA)             --}}
    {{-- ========================================== --}}
    @if($showModalExito)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 no-imprimir animate-fade-in-down">
        <div class="bg-white p-8 rounded-2xl shadow-2xl text-center max-w-sm w-full border-t-4 border-green-500">
            <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-800 mb-2">¡Venta Exitosa!</h3>
            <p class="text-gray-500 mb-8 text-sm">El recibo #{{ str_pad($ultimoIdVenta, 6, '0', STR_PAD_LEFT) }} se guardó correctamente.</p>
            
            <div class="flex flex-col gap-3">
                <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-3 rounded-xl font-bold hover:bg-black transition w-full flex items-center justify-center gap-2 shadow-lg">
                    <i class="fa-solid fa-print"></i> Imprimir Recibo
                </button>
                <button wire:click="cerrarModalExito" class="bg-green-100 text-green-700 px-6 py-3 rounded-xl font-bold hover:bg-green-200 transition w-full">
                    Nueva Venta
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL DE HISTORIAL Y ANULACIÓN (Mantenido intacto pero estilizado) --}}
    @if($showModalHistorial)
        <div class="fixed inset-0 flex items-center justify-center z-50 no-imprimir">
            <div class="absolute inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm" wire:click="cerrarHistorial"></div>
            
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl p-6 z-50 relative max-h-[90vh] flex flex-col animate-fade-in-down">
                
                <div class="flex justify-between items-center mb-5 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-bold text-gray-800">
                        <i class="fas fa-history text-orange-500 mr-2"></i> Historial de Ventas ({{ date('d/m/Y') }})
                    </h3>
                    <button wire:click="cerrarHistorial" class="text-gray-400 hover:text-red-500 text-2xl transition">&times;</button>
                </div>

                <div class="overflow-y-auto flex-1 custom-scrollbar pr-2">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider text-xs">Recibo</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider text-xs">Hora</th>
                                <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider text-xs">Estudiante</th>
                                <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider text-xs">Total</th>
                                <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider text-xs">Estado</th>
                                <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider text-xs">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ventasDelDia as $v)
                                <tr class="hover:bg-gray-50 transition-colors {{ $v->estado == 'anulada' ? 'bg-red-50 opacity-60' : '' }}">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">#{{ str_pad($v->id_venta, 6, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($v->fecha_venta)->format('H:i') }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-800">{{ $v->estudiante->nombre }} {{ $v->estudiante->apellido }}</td>
                                    <td class="px-4 py-3 text-right font-black text-orange-600">{{ number_format($v->monto_total, 2) }} Bs</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($v->estado == 'finalizada')
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-[10px] font-black tracking-wider border border-green-200">EXITOSA</span>
                                        @else
                                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-[10px] font-black tracking-wider border border-red-200">ANULADA</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($v->estado == 'finalizada')
                                            <button wire:click="anularVenta({{ $v->id_venta }})" 
                                                onclick="confirm('¿Estás seguro de anular esta venta? Se revertirá el stock y el dinero en caja.') || event.stopImmediatePropagation()"
                                                class="text-red-400 hover:text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg transition text-xs font-bold border border-transparent hover:border-red-100" title="Anular Venta">
                                                <i class="fas fa-ban mr-1"></i> Anular
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-gray-400">
                                        <i class="fa-solid fa-receipt text-3xl mb-2 text-gray-200"></i>
                                        <p>No hay ventas registradas hoy.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- RECIBO PARA IMPRIMIR (MISMO TAMAÑO)        --}}
    {{-- ========================================== --}}
    @if($datosRecibo)
    <div class="zona-impresion bg-white">
        <div class="text-center mb-4 border-b-2 border-dashed border-gray-400 pb-4">
            <h1 class="font-black text-2xl uppercase tracking-widest">IGLA POTOSÍ</h1>
            <p class="text-sm">Instituto Técnico Gastronómico</p>
            <p class="text-sm">Telfs 74289575</p>
            <p class="text-xs">Calle Tarija #30, Potosí - Bolivia</p>
        </div>

        <div class="text-center mb-4">
            <h2 class="font-bold text-lg uppercase">Comprobante de Venta</h2>
            <p class="text-sm">Nro: <strong>{{ $datosRecibo['nro_recibo'] }}</strong></p>
        </div>

        <div class="mb-4 text-sm border-b border-gray-300 pb-2">
            <p><strong>Fecha:</strong> {{ $datosRecibo['fecha'] }}</p>
            <p><strong>Estudiante:</strong> {{ $datosRecibo['estudiante'] }}</p>
            <p><strong>CI:</strong> {{ $datosRecibo['ci'] }}</p>
            <p><strong>Cajero(a):</strong> {{ $datosRecibo['cajero'] }}</p>
        </div>

        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="border-b-2 border-gray-800">
                    <th class="text-left py-1">Cant.</th>
                    <th class="text-left py-1">Descripción</th>
                    <th class="text-right py-1">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosRecibo['items'] as $item)
                <tr class="border-b border-dashed border-gray-200">
                    <td class="py-2 pr-2 font-bold">{{ $item['cantidad'] }}</td>
                    <td class="py-2 pr-2">
                        <div class="font-bold">{{ $item['nombre'] }}</div>
                        <div class="text-[10px] text-gray-500">{{ number_format($item['precio'], 2) }} Bs c/u</div>
                    </td>
                    <td class="py-2 text-right font-mono font-bold">{{ number_format($item['subtotal'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-end mb-8">
            <div class="w-3/4 text-sm">
                <div class="flex justify-between font-black text-base border-t-2 border-gray-800 pt-1">
                    <span>TOTAL Bs:</span>
                    <span>{{ number_format($datosRecibo['total'], 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600 mt-1">
                    <span>Efectivo/Ingresado:</span>
                    <span>{{ number_format($datosRecibo['ingresado'], 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Cambio:</span>
                    <span>{{ number_format($datosRecibo['cambio'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-gray-500 border-t border-gray-300 pt-4">
            <p>Conserve este comprobante para cualquier reclamo.</p>
            <p class="font-bold mt-1">¡Gracias por su preferencia!</p>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
{{-- CSS MÁGICO PARA IMPRESIÓN (EL MISMO)       --}}
{{-- ========================================== --}}
<style>
    .zona-impresion { display: none; }

    @media print {
        nav, aside, .ocultar-al-imprimir, .no-imprimir { display: none !important; }

        @page { margin: 0 !important; size: auto; }
        
        body, html { margin: 0 !important; padding: 0 !important; background-color: white !important; }

        main, main > div, .container, .px-4 {
            margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important;
            border-radius: 0 !important; background: white !important; max-width: 100% !important;
        }

        .zona-impresion {
            display: block !important; position: absolute !important; top: 0 !important; left: 0 !important;
            width: 100% !important; padding: 1.5cm 2cm !important; border: none !important; box-shadow: none !important;
            background: transparent !important; border-radius: 0 !important; color: black !important;
        }

        .zona-impresion * { color: black !important; font-family: Arial, Helvetica, sans-serif !important; background: transparent !important; }
        
        .zona-impresion p, .zona-impresion td, .zona-impresion th, .zona-impresion span, .zona-impresion div { font-size: 11pt !important; line-height: 1.4 !important; }
        .zona-impresion h1 { font-size: 18pt !important; margin-bottom: 5px !important; }
        .zona-impresion h2 { font-size: 14pt !important; margin-bottom: 5px !important; text-transform: uppercase !important; }

        .zona-impresion table { width: 100% !important; table-layout: auto !important; border-collapse: collapse !important; border: none !important; }
        .zona-impresion th, .zona-impresion td { border: none !important; border-bottom: 1px dashed #ccc !important; padding: 6px 0 !important; }
        .zona-impresion thead th { border-bottom: 2px solid black !important; }
    }
</style>

</div> 

