<div class="px-4 pb-8">
    <div class="ocultar-al-imprimir">

        {{-- HEADER --}}
        <div class="mb-8">
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">INSCRIPCIÓN POR MÓDULOS</h2>
            <p class="text-sm text-gray-500 mt-1">Registra a los estudiantes en módulos específicos y procesa sus pagos.</p>
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
                        <div class="w-full flex items-center justify-between border-2 border-green-500 bg-green-50 rounded-lg p-3 transition-all animate-fade-in-down">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-circle-check text-green-500 text-2xl bg-white rounded-full shadow-sm"></i>
                                <div>
                                    <span class="font-bold text-green-800 text-lg">{{ $estudianteSeleccionado->nombre }} {{ $estudianteSeleccionado->apellido }}</span>
                                    <span class="text-green-600 text-xs block font-mono">CI: {{ $estudianteSeleccionado->ci }}</span>
                                    
                                    {{-- Etiqueta PUP --}}
                                    <div class="mt-1.5">
                                        @if($tienePUP)
                                            <span class="bg-green-200 text-green-800 text-[10px] font-black px-2 py-1 rounded border border-green-300 shadow-sm">
                                                <i class="fas fa-check-circle"></i> PUP AL DÍA
                                            </span>
                                        @else
                                            <span class="bg-red-100 text-red-700 text-[10px] font-black px-2 py-1 rounded border border-red-200 shadow-sm">
                                                <i class="fas fa-exclamation-circle"></i> PUP PENDIENTE
                                            </span>
                                            <span class="text-[10px] text-red-500 ml-1 italic font-bold">Se agregará al carrito</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button wire:click="$set('estudianteSeleccionado', null)" class="text-red-400 hover:text-red-600 transition bg-white rounded-full px-4 py-2 shadow-sm text-xs font-bold border border-red-100">
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
                                    <li wire:key="search-est-{{ $est->id_estudiante }}" wire:click="seleccionarEstudiante({{ $est->id_estudiante }})" 
                                        class="p-3 cursor-pointer hover:bg-orange-50 transition flex items-center gap-3 text-sm">
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
                </div>

                {{-- 2. Filtros de Categoría --}}
                <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                    <button wire:click="filtrarCategoria(null)" 
                        class="px-4 py-1.5 rounded-full text-sm font-bold shadow-sm transition-all whitespace-nowrap {{ is_null($id_categoria) ? 'bg-gray-800 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        Todos
                    </button>
                    @foreach($categorias as $cat)
                        <button wire:click="filtrarCategoria({{ $cat->id_categoria_modulo }})" 
                            class="px-4 py-1.5 rounded-full text-sm font-bold shadow-sm transition-all whitespace-nowrap
                            {{ $id_categoria == $cat->id_categoria_modulo ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-600 border border-gray-200 hover:bg-orange-50 hover:text-orange-600' }}">
                            {{ $cat->nombre }}
                        </button>
                    @endforeach
                </div>

                {{-- Buscador Módulos --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" wire:model.live="searchModulo" placeholder="Buscar módulo específico..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 focus:bg-white text-sm">
                </div>

                {{-- 3. Grid Módulos --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($modulos as $mod)
                        <div wire:key="modulo-{{ $mod->id_modulo }}"
                            class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-orange-300 hover:shadow-md transition-all flex flex-col justify-between h-full group">
                            
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block mb-1">{{ $mod->categoria->nombre ?? 'General' }}</span>
                                <h5 class="font-bold text-gray-800 leading-tight mb-2 group-hover:text-orange-600 transition-colors">{{ $mod->nombre }}</h5>
                            </div>

                            <div class="mt-4 flex justify-between items-end">
                                <span class="block text-lg font-black text-gray-800">{{ number_format($mod->costo, 2) }} <span class="text-xs text-gray-400">Bs</span></span>
                                <button wire:click="agregarAlCarrito({{ $mod->id_modulo }})" 
                                    class="bg-orange-100 text-orange-600 hover:bg-orange-500 hover:text-white w-10 h-10 rounded-full flex items-center justify-center transition-colors">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-1 sm:col-span-2 text-center py-12 text-gray-400 bg-white rounded-xl border border-dashed border-gray-200">
                            <i class="fa-solid fa-cubes text-4xl mb-3 text-gray-300"></i>
                            <p>No se encontraron módulos con esa búsqueda.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- COLUMNA DERECHA: CARRITO Y PAGO --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 sticky top-4 overflow-hidden flex flex-col max-h-[calc(100vh-40px)]">
                    
                    {{-- Encabezado Carrito --}}
                    <div class="bg-gray-800 text-white p-4 flex justify-between items-center">
                        <span class="font-bold tracking-wide"><i class="fas fa-shopping-cart text-orange-400 mr-2"></i> INSCRIPCIÓN</span>
                        <span class="bg-gray-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ count($carrito) }} items</span>
                    </div>

                    {{-- Lista de Items --}}
                    <div class="p-4 space-y-3 overflow-y-auto flex-1 custom-scrollbar min-h-[250px]">
                        @if(session()->has('warning'))
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-3 rounded text-sm mb-3 font-medium shadow-sm">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('warning') }}
                            </div>
                        @endif
                        @if(session()->has('error'))
                            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-3 rounded text-sm mb-3 font-medium shadow-sm">
                                <i class="fa-solid fa-circle-xmark mr-1"></i> {{ session('error') }}
                            </div>
                        @endif

                        @forelse($carrito as $index => $item)
                            <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border {{ $item['tipo'] == 'pup' ? 'border-orange-200 bg-orange-50' : 'border-gray-100' }}">
                                <div class="flex-1 pr-2">
                                    <p class="text-sm font-bold text-gray-800 leading-tight mb-0.5">{{ $item['nombre'] }}</p>
                                    <p class="text-[10px] uppercase font-black tracking-wider {{ $item['tipo'] == 'pup' ? 'text-orange-500' : 'text-gray-400' }}">
                                        {{ $item['tipo'] == 'pup' ? 'Requisito Obligatorio' : 'Módulo' }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-black text-gray-800">{{ number_format($item['precio'], 2) }}</span>
                                    <button wire:click="quitarDelCarrito({{ $index }})" class="text-gray-400 hover:text-red-500 bg-white border border-gray-200 hover:bg-red-50 w-7 h-7 rounded flex items-center justify-center transition" title="Quitar item">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-gray-400">
                                <i class="fa-solid fa-basket-shopping text-4xl mb-3 text-gray-200"></i>
                                <p class="text-sm">No hay módulos seleccionados</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Sección de Pago --}}
                    <div class="p-5 bg-gray-50 border-t border-gray-200">
                        
                        <div class="flex justify-between items-end mb-5">
                            <span class="text-gray-500 font-bold text-sm uppercase tracking-wider">Total a Pagar</span>
                            <span class="text-3xl font-black text-orange-600 leading-none">{{ number_format($total, 2) }} <span class="text-sm text-orange-400">Bs</span></span>
                        </div>

                        {{-- INTERFAZ DE PAGO MÚLTIPLE --}}
                        @if($total > 0)
                            <div class="space-y-3 mb-5">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Método de Pago</label>
                                
                                @foreach($metodosPago as $metodo)
                                    <div class="flex shadow-sm rounded-lg overflow-hidden border border-gray-300 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 transition-all bg-white">
                                        <span class="inline-flex items-center justify-center px-3 bg-gray-100 text-gray-600 text-xs font-bold w-24 border-r border-gray-300">
                                            {{ $metodo->nombre }}
                                        </span>
                                        <input type="number" step="0.50" wire:model.live="montosPago.{{ $metodo->id_metodo_pago }}" 
                                            class="flex-1 w-full px-3 py-2 border-none text-sm font-bold text-gray-800 focus:ring-0 bg-white" 
                                            placeholder="0.00">
                                        <button wire:click="llenarSaldo({{ $metodo->id_metodo_pago }})" class="px-3 bg-white text-gray-400 hover:text-orange-500 transition border-l border-gray-200" title="Autocompletar saldo restante">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                    </div>
                                @endforeach
                                
                                <div class="flex justify-between text-xs pt-2 border-t border-gray-200 mt-2">
                                    <span>Ingresado: <strong class="{{ $totalIngresado >= $total - 0.1 ? 'text-green-600' : 'text-red-500' }} font-mono text-sm">{{ number_format($totalIngresado, 2) }}</strong></span>
                                    <span>Cambio: <strong class="font-mono text-sm text-gray-800">{{ number_format(max(0, $totalIngresado - $total), 2) }}</strong></span>
                                </div>
                                @error('pago') 
                                    <span class="text-red-500 text-xs block font-bold mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> 
                                @enderror
                            </div>

                            <button wire:click="finalizarInscripcion" 
                                wire:loading.attr="disabled"
                                wire:target="finalizarInscripcion"
                                class="w-full bg-gray-800 text-white py-3.5 rounded-xl font-bold shadow-lg hover:bg-black transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ $totalIngresado < $total - 0.1 ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="finalizarInscripcion"><i class="fa-solid fa-check-circle"></i> Confirmar Inscripción</span>
                                <span wire:loading wire:target="finalizarInscripcion"><i class="fa-solid fa-spinner fa-spin"></i> Procesando...</span>
                            </button>
                        @else
                            <button disabled class="w-full bg-gray-200 text-gray-400 py-3.5 rounded-xl font-bold cursor-not-allowed flex items-center justify-center gap-2">
                                <i class="fa-solid fa-check-circle"></i> Confirmar Inscripción
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
            <h3 class="text-2xl font-black text-gray-800 mb-2">¡Inscripción Exitosa!</h3>
            <p class="text-gray-500 mb-8 text-sm">Se han registrado los módulos y los pagos correctamente en el sistema.</p>
            
            <div class="flex flex-col gap-3">
                <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-3 rounded-xl font-bold hover:bg-black transition w-full flex items-center justify-center gap-2 shadow-lg">
                    <i class="fa-solid fa-print"></i> Imprimir Comprobante
                </button>

                <button wire:click="descargarReciboPdf" wire:loading.attr="disabled" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition w-full flex items-center justify-center gap-2 shadow-lg disabled:opacity-50">
                    <span wire:loading.remove wire:target="descargarReciboPdf"><i class="fa-solid fa-file-pdf"></i> Descargar PDF</span>
                    <span wire:loading wire:target="descargarReciboPdf"><i class="fa-solid fa-spinner fa-spin"></i> Generando PDF...</span>
                </button>
                
                <button wire:click="cerrarModalExito" class="bg-green-50 text-green-700 px-6 py-3 rounded-xl font-bold hover:bg-green-100 transition w-full">
                    Nueva Inscripción
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- RECIBO DE INSCRIPCIÓN (DISEÑO COMPACTO)    --}}
    {{-- ========================================== --}}
    @if($datosRecibo)
    <div class="zona-impresion bg-white">
        
        {{-- 1. Cabecera del Instituto (Compacta) --}}
        <div class="flex items-center justify-between mb-3 border-b-2 border-dashed border-gray-400 pb-2">
            {{-- Lado Izquierdo: LOGO --}}
            <div class="w-1/4">
                <img src="{{ asset('img/LOGO_POTOSI_01.png') }}" alt="Logo IGLA" class="max-h-16 object-contain grayscale" style="filter: grayscale(100%);">
            </div>
            
            {{-- Lado Derecho: Textos --}}
            <div class="w-3/4 text-right">
                <h1 class="font-black text-2xl uppercase tracking-widest leading-none mb-1">IGLA POTOSÍ</h1>
                <p class="text-xs text-gray-600 font-bold mt-1">Instituto Técnico Gastronómico</p>
                <p class="text-[10px] text-gray-500 mt-0.5">Telfs 74289575 &nbsp;|&nbsp; Calle Tarija #30, Potosí</p>
            </div>
        </div>

        {{-- 2. Título y Número --}}
        <div class="flex justify-between items-end mb-4 border-b border-gray-800 pb-1">
            <h2 class="font-bold text-lg uppercase tracking-wide">Comprobante de Inscripción</h2>
            <p class="text-sm">Nro: <span class="font-bold text-lg">{{ $datosRecibo['nro_recibo'] }}</span></p>
        </div>

        {{-- 3. Datos a los extremos (Izquierda: Estudiante | Derecha: Cajero) --}}
        <div class="flex justify-between mb-4 text-sm bg-gray-50 p-2 rounded-lg border border-gray-100">
            <div class="text-left w-1/2 pr-2">
                <p><span class="text-gray-500 uppercase text-[10px] font-bold inline-block mr-1">Estudiante:</span> {{ $datosRecibo['estudiante'] }}</p>
                <p><span class="text-gray-500 uppercase text-[10px] font-bold inline-block mr-1">CI:</span> {{ $datosRecibo['ci'] }}</p>
            </div>
            
            <div class="text-right w-1/2 pl-2">
                <p><span class="text-gray-500 uppercase text-[10px] font-bold inline-block mr-1">Fecha de emisión:</span> {{ $datosRecibo['fecha'] }}</p>
                <p><span class="text-gray-500 uppercase text-[10px] font-bold inline-block mr-1">Cajero(a):</span> {{ $datosRecibo['cajero'] }}</p>
            </div>
        </div>

        {{-- 4. Detalle de Compra (Tabla más ajustada) --}}
        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="border-b-2 border-gray-800">
                    <th class="text-left py-1">Descripción Académica</th>
                    <th class="text-right py-1">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosRecibo['items'] as $item)
                <tr class="border-b border-dashed border-gray-200">
                    <td class="py-1.5 pr-2 align-top">
                        <div class="font-bold text-base leading-tight">{{ $item['nombre'] }}</div>
                        <div class="text-[10px] text-gray-500 mt-0.5 uppercase tracking-wider">{{ $item['tipo'] == 'pup' ? 'Pago Único' : 'Módulo' }}</div>
                    </td>
                    <td class="py-1.5 text-right font-mono font-bold align-top text-lg">{{ number_format($item['precio'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- 5. Totales --}}
        <div class="flex justify-end mb-6">
            <div class="w-3/4 sm:w-1/2 text-sm">
                <div class="flex justify-between font-black text-lg border-t-2 border-gray-800 pt-1.5">
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

        {{-- 6. Mensaje Final --}}
        <div class="text-center text-[11px] text-gray-500 border-t border-gray-300 pt-3">
            <p>Conserve este comprobante para cualquier reclamo.</p>
            <p class="font-bold text-gray-800 mt-0.5">¡Gracias por ser parte de IGLA!</p>
        </div>

    </div>
    @endif

    {{-- ========================================== --}}
    {{-- CSS MÁGICO PARA IMPRESIÓN                  --}}
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

            /* Habilitar Flexbox en la impresora */
            .zona-impresion .flex { display: flex !important; }

            .zona-impresion {
                display: block !important; position: absolute !important; top: 0 !important; left: 0 !important;
                width: 100% !important; max-width: 100% !important;
                padding: 1cm 1.5cm !important; /* PADDING REDUCIDO PARA COMPACTAR */
                border: none !important; box-shadow: none !important;
                background: transparent !important; border-radius: 0 !important; color: black !important;
            }

            .zona-impresion * { color: black !important; font-family: Arial, Helvetica, sans-serif !important; background: transparent !important; }
            
            .zona-impresion p, .zona-impresion td, .zona-impresion th, .zona-impresion span, .zona-impresion div { font-size: 11pt !important; line-height: 1.3 !important; }
            .zona-impresion h1 { font-size: 16pt !important; margin-bottom: 2px !important; }
            .zona-impresion h2 { font-size: 13pt !important; margin-bottom: 0 !important; text-transform: uppercase !important; }

            .zona-impresion table { width: 100% !important; table-layout: auto !important; border-collapse: collapse !important; border: none !important; }
            .zona-impresion th, .zona-impresion td { border: none !important; border-bottom: 1px dashed #ccc !important; padding: 4px 0 !important; }
            .zona-impresion thead th { border-bottom: 2px solid black !important; }
        }
    </style>
</div>
