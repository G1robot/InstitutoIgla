<div class="px-4 pb-8">
    <div class="ocultar-al-imprimir">
        <div class="max-w-7xl mx-auto pb-10">
            {{-- HEADER Y CONTROLES --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6 flex flex-col md:flex-row justify-between items-center gap-4 relative z-20">
                <div class="mb-8 border-l-4 border-orange-500 pl-4">
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">CONTROL SEMANAL DE INSUMOS</h2>
                    <p class="text-sm text-gray-500 mt-1">Registro rápido de pagos, faltas y licencias.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-end">
            
                    {{-- NUEVO: Selector de Tipo de Insumo --}}
                    <div class="relative w-full sm:w-auto min-w-[180px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-box text-gray-400"></i>
                        </div>
                        <select wire:model="articulo_seleccionado" 
                            class="w-full pl-10 pr-3 py-2 border border-blue-300 rounded-lg text-sm font-bold text-gray-700 focus:ring-blue-500 focus:border-blue-500 bg-blue-50 focus:bg-white transition" title="Tipo de Insumo a cobrar">
                            @foreach($articulosInsumo as $art)
                                <option value="{{ $art->id_articulo }}">{{ $art->nombre }} ({{ number_format($art->precio, 2) }} Bs)</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Método de Pago --}}
                    <div class="relative w-full sm:w-auto min-w-[140px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-wallet text-gray-400"></i>
                        </div>
                        <select wire:model="metodo_pago_seleccionado" 
                            class="w-full pl-10 pr-3 py-2 border border-orange-300 rounded-lg text-sm font-bold text-gray-700 focus:ring-orange-500 focus:border-orange-500 bg-orange-50 focus:bg-white transition" title="Método de pago">
                            @foreach($metodosPago as $metodo)
                                <option value="{{ $metodo->id_metodo_pago }}">{{ $metodo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Selector de Fecha --}}
                    <div class="relative w-full sm:w-auto min-w-[150px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-regular fa-calendar text-gray-400"></i>
                        </div>
                        <input type="date" wire:model.live="fecha_semana" 
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm font-bold text-gray-700 focus:ring-orange-500 focus:border-orange-500 bg-gray-50 focus:bg-white transition">
                    </div>

                    {{-- Buscador --}}
                    <div class="relative w-full sm:w-auto min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" 
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500" placeholder="Buscar alumno...">
                    </div>
                </div>
            </div>

            @error('general') 
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                    <p class="text-red-700 font-bold text-sm"><i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ $message }}</p>
                </div>
            @enderror
        

            {{-- TABLA DE REGISTRO --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Estudiante</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider w-40">Estado de Semana</th>
                                <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Acciones Rápidas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($estudiantes as $est)
                                @php
                                    // Verificamos si ya tiene registro en esta semana
                                    $registroActual = $est->controlInsumos->first();
                                @endphp
                                
                                <tr wire:key="estudiante-{{ $est->id_estudiante }}" class="hover:bg-orange-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-gray-900">{{ $est->apellido }} {{ $est->nombre }}</div>
                                        <div class="text-xs text-gray-500">CI: {{ $est->ci }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($registroActual)
                                            @if($registroActual->estado === 'pagado')
                                                <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-full text-xs font-bold border border-green-200">
                                                    <i class="fa-solid fa-check-double mr-1"></i> PAGADO
                                                </span>
                                            @elseif($registroActual->estado === 'falta')
                                                <span class="bg-red-100 text-red-700 px-3 py-1.5 rounded-full text-xs font-bold border border-red-200">
                                                    <i class="fa-solid fa-xmark mr-1"></i> FALTA
                                                </span>
                                            @elseif($registroActual->estado === 'licencia')
                                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-full text-xs font-bold border border-yellow-200">
                                                    <i class="fa-solid fa-hand-holding-medical mr-1"></i> LICENCIA
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic text-xs">Sin registrar</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex justify-center items-center gap-2">
                                            @if(!$registroActual)
                                                {{-- BOTONES DE REGISTRO RÁPIDO --}}
                                                <button wire:click="registrarEstado({{ $est->id_estudiante }}, 'pagado')" wire:loading.attr="disabled"
                                                    class="bg-green-50 text-green-600 hover:bg-green-600 hover:text-white px-3 py-1.5 rounded-lg transition border border-green-200 shadow-sm text-xs font-bold disabled:opacity-50" title="Cobrar Insumo">
                                                    <i class="fa-solid fa-sack-dollar"></i> Cobrar
                                                </button>
                                                
                                                <button wire:click="registrarEstado({{ $est->id_estudiante }}, 'falta')" wire:loading.attr="disabled"
                                                    class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1.5 rounded-lg transition border border-red-200 shadow-sm text-xs font-bold disabled:opacity-50" title="Marcar Falta">
                                                    <i class="fa-solid fa-user-xmark"></i> Falta
                                                </button>

                                                <button wire:click="registrarEstado({{ $est->id_estudiante }}, 'licencia')" wire:loading.attr="disabled"
                                                    class="bg-yellow-50 text-yellow-600 hover:bg-yellow-500 hover:text-white px-3 py-1.5 rounded-lg transition border border-yellow-200 shadow-sm text-xs font-bold disabled:opacity-50" title="Licencia">
                                                    <i class="fa-solid fa-notes-medical"></i> Lic.
                                                </button>
                                            @endif

                                            <button wire:click="abrirCobroMultiple({{ $est->id_estudiante }})" wire:loading.attr="disabled"
                                                class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg transition border border-blue-200 shadow-sm text-xs font-bold disabled:opacity-50 ml-1" title="Cobrar Múltiples Semanas">
                                                <i class="fa-solid fa-layer-group"></i> Múltiple
                                            </button>

                                            {{-- BOTÓN HISTORIAL (Siempre visible) --}}
                                            <button wire:click="abrirHistorial({{ $est->id_estudiante }})" wire:loading.attr="disabled"
                                                class="bg-gray-50 text-gray-600 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg transition border border-gray-200 shadow-sm text-xs font-bold disabled:opacity-50 ml-2" title="Ver Historial">
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">No se encontraron estudiantes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $estudiantes->links() }}
                </div>
            </div>
        </div>

        {{-- MODAL DE HISTORIAL --}}
        @if($showModalHistorial)
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" wire:click="cerrarHistorial"></div>

                    <div class="relative inline-block w-full max-w-lg p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl animate-fade-in-up">
                        <div class="flex justify-between items-center mb-5 pb-3 border-b border-gray-100">
                            <div>
                                <h3 class="text-lg font-black text-gray-800 uppercase">Historial de Insumos</h3>
                                <p class="text-sm text-gray-500">{{ $estudianteHistorial->nombre }} {{ $estudianteHistorial->apellido }}</p>
                            </div>
                            <button wire:click="cerrarHistorial" class="text-gray-400 hover:text-red-500 transition">
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>

                        <div class="max-h-96 overflow-y-auto pr-2">
                            @forelse($historialInsumos as $historial)
                                <div wire:key="historial-{{ $historial->id_control_insumo }}" class="flex justify-between items-center p-3 mb-2 bg-gray-50 rounded-lg border border-gray-100">
                                    <div>
                                        <span class="block font-bold text-gray-800 text-sm">
                                            <i class="fa-regular fa-calendar-days text-gray-400 mr-1"></i> 
                                            {{ \Carbon\Carbon::parse($historial->fecha_semana)->format('d / m / Y') }}
                                        </span>
                                        @if($historial->id_venta)
                                            <span class="text-[10px] text-gray-400 font-mono">Recibo Venta #{{ $historial->id_venta }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        @if($historial->estado === 'pagado')
                                            <span class="text-green-600 font-bold text-xs bg-green-100 px-2 py-1 rounded"><i class="fa-solid fa-check"></i> Pagado</span>
                                        @elseif($historial->estado === 'falta')
                                            <span class="text-red-600 font-bold text-xs bg-red-100 px-2 py-1 rounded"><i class="fa-solid fa-xmark"></i> Falta</span>
                                        @else
                                            <span class="text-yellow-600 font-bold text-xs bg-yellow-100 px-2 py-1 rounded"><i class="fa-solid fa-hand-holding-medical"></i> Licencia</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-gray-400 text-sm">No hay registros previos para este alumno.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
        {{-- MODAL DE COBRO MÚLTIPLE --}}
        @if($showModalMultiple)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" wire:click="cerrarModalMultiple"></div>

                <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl animate-fade-in-up">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-lg font-black text-gray-800 uppercase"><i class="fa-solid fa-layer-group text-blue-500 mr-2"></i> Cobro Múltiple</h3>
                            <p class="text-sm text-gray-500">{{ $estudianteMultiple->nombre }} {{ $estudianteMultiple->apellido }}</p>
                        </div>
                        <button wire:click="cerrarModalMultiple" class="text-gray-400 hover:text-red-500 transition">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    @error('multiple') 
                        <div class="mb-4 bg-red-50 text-red-600 p-3 rounded-lg text-xs font-bold border border-red-200">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ $message }}
                        </div>
                    @enderror

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fechas a cobrar:</label>
                        <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                            @foreach($fechasMultiple as $index => $fecha)
                                <div class="flex items-center gap-2">
                                    <input type="date" wire:model="fechasMultiple.{{ $index }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-bold text-gray-700 focus:ring-blue-500 focus:border-blue-500">
                                    @if(count($fechasMultiple) > 1)
                                        <button wire:click="quitarFechaMultiple({{ $index }})" class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white w-9 h-9 rounded-lg flex items-center justify-center transition border border-red-200">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        
                        <button wire:click="agregarFechaMultiple" class="mt-3 w-full border-2 border-dashed border-gray-300 text-gray-500 hover:border-blue-500 hover:text-blue-600 py-2 rounded-lg font-bold text-sm transition">
                            <i class="fa-solid fa-plus mr-1"></i> Añadir otra semana
                        </button>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-5">
                        <div class="flex justify-between items-center font-black text-gray-800 text-lg">
                            <span>TOTAL:</span>
                            @php
                                $art = \App\Models\ArticuloModel::find($articulo_seleccionado);
                                $totalModal = $art ? $art->precio * count($fechasMultiple) : 0;
                            @endphp
                            <span class="text-blue-600">{{ number_format($totalModal, 2) }} Bs</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1 text-right">Se cobrará usando el método configurado en la barra superior.</div>
                    </div>

                    <button wire:click="procesarCobroMultiple" wire:loading.attr="disabled" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition flex items-center justify-center gap-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="procesarCobroMultiple"><i class="fa-solid fa-cash-register"></i> Procesar y Generar Recibo</span>
                        <span wire:loading wire:target="procesarCobroMultiple"><i class="fa-solid fa-spinner fa-spin"></i> Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
    {{-- ========================================== --}}
    {{-- MODAL DE ÉXITO (MODO PANTALLA)             --}}
    {{-- ========================================== --}}
    @if($showModalExito)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 no-imprimir animate-fade-in-down">
        <div class="bg-white p-8 rounded-2xl shadow-2xl text-center max-w-sm w-full border-t-4 border-green-500">
            <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-800 mb-2">¡Insumo Cobrado!</h3>
            <p class="text-gray-500 mb-8 text-sm">El recibo #{{ str_pad($ultimoIdVenta, 6, '0', STR_PAD_LEFT) }} se guardó correctamente en caja.</p>
            
            <div class="flex flex-col gap-3">
                <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-3 rounded-xl font-bold hover:bg-black transition w-full flex items-center justify-center gap-2 shadow-lg">
                    <i class="fa-solid fa-print"></i> Imprimir Recibo
                </button>

                <button wire:click="descargarReciboPdf" wire:loading.attr="disabled" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition w-full flex items-center justify-center gap-2 shadow-lg disabled:opacity-50">
                    <span wire:loading.remove wire:target="descargarReciboPdf"><i class="fa-solid fa-file-pdf"></i> Descargar PDF</span>
                    <span wire:loading wire:target="descargarReciboPdf"><i class="fa-solid fa-spinner fa-spin"></i> Generando PDF...</span>
                </button>

                <button wire:click="cerrarModalExito" class="bg-green-100 text-green-700 px-6 py-3 rounded-xl font-bold hover:bg-green-200 transition w-full">
                    Siguiente Alumno
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- RECIBO PARA IMPRIMIR (Reutilizamos tu código) --}}
    {{-- ========================================== --}}
    @if($datosRecibo)
    <div class="zona-impresion bg-white">
        <div class="flex items-center justify-between mb-3 border-b-2 border-dashed border-gray-400 pb-2">
            <div class="w-1/4">
                <img src="{{ asset('img/LOGO_POTOSI_01.png') }}" alt="Logo IGLA" class="max-h-16 object-contain grayscale" style="filter: grayscale(100%);">
            </div>
            <div class="w-3/4 text-right">
                <h1 class="font-black text-2xl uppercase tracking-widest leading-none mb-1">IGLA POTOSÍ</h1>
                <p class="text-xs text-gray-600 font-bold mt-1">Instituto Técnico Gastronómico</p>
                <p class="text-[10px] text-gray-500 mt-0.5">Telfs 74289575 &nbsp;|&nbsp; Calle Tarija #30, Potosí</p>
            </div>
        </div>

        <div class="flex justify-between items-end mb-4 border-b border-gray-800 pb-1">
            <h2 class="font-bold text-lg uppercase tracking-wide">Comprobante de Venta</h2>
            <p class="text-sm">Nro: <span class="font-bold text-lg">{{ $datosRecibo['nro_recibo'] }}</span></p>
        </div>

        <div class="flex justify-between mb-4 text-sm bg-gray-50 p-2 rounded-lg border border-gray-100">
            <div class="text-left">
                <p><span class="text-gray-500 uppercase text-[10px] font-bold inline-block mr-1">Estudiante:</span> {{ $datosRecibo['estudiante'] }}</p>
                <p><span class="text-gray-500 uppercase text-[10px] font-bold inline-block mr-1">CI:</span> {{ $datosRecibo['ci'] }}</p>
            </div>
            <div class="text-right">
                <p><span class="text-gray-500 uppercase text-[10px] font-bold inline-block mr-1">Fecha de emisión:</span> {{ $datosRecibo['fecha'] }}</p>
                <p><span class="text-gray-500 uppercase text-[10px] font-bold inline-block mr-1">Cajero(a):</span> {{ $datosRecibo['cajero'] }}</p>
            </div>
        </div>

        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="border-b-2 border-gray-800">
                    <th class="text-left py-1 w-12">Cant.</th>
                    <th class="text-left py-1">Descripción</th>
                    <th class="text-right py-1">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosRecibo['items'] as $item)
                <tr class="border-b border-dashed border-gray-200">
                    <td class="py-1.5 pr-2 font-bold align-top">{{ $item['cantidad'] }}</td>
                    <td class="py-1.5 pr-2">
                        <div class="font-bold leading-tight">{{ $item['nombre'] }}</div>
                        <div class="text-[10px] text-gray-500">{{ number_format($item['precio'], 2) }} Bs</div>
                    </td>
                    <td class="py-1.5 text-right font-mono font-bold align-top">{{ number_format($item['subtotal'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-end mb-6">
            <div class="w-2/3 sm:w-1/2 text-sm">
                <div class="flex justify-between font-black text-base border-t-2 border-gray-800 pt-1">
                    <span>TOTAL Bs:</span>
                    <span>{{ number_format($datosRecibo['total'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="text-center text-[11px] text-gray-500 border-t border-gray-300 pt-3">
            <p>Conserve este comprobante para cualquier reclamo.</p>
            <p class="font-bold text-gray-800 mt-0.5">¡Gracias por su preferencia!</p>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- CSS MÁGICO PARA IMPRESIÓN (El mismo que usas) --}}
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
                width: 100% !important; padding: 1cm 1.5cm !important; border: none !important; box-shadow: none !important;
                background: transparent !important; border-radius: 0 !important; color: black !important;
            }
            .zona-impresion .flex { display: flex !important; }
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