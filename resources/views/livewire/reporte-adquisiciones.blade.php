<div class="container mx-auto px-4 py-6 print:p-8">

    {{-- ========================================== --}}
    {{-- 1. ENCABEZADOS Y BÚSQUEDA WEB (Solo Pantalla)--}}
    {{-- ========================================== --}}
    <div class="print:hidden">
        <h2 class="text-center text-3xl font-bold mb-8 text-gray-800 border-b pb-4">
            REPORTE INDIVIDUAL DE ADQUISICIONES
        </h2>

        {{-- BUSCADOR DE ESTUDIANTE --}}
        @if(!$estudianteSeleccionado)
            <div class="max-w-3xl mx-auto mb-10 relative z-30">
                <label class="block text-gray-700 text-sm font-bold mb-2">Seleccione un Estudiante:</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="searchEstudiante"
                        class="w-full p-4 pl-12 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 text-lg"
                        placeholder="Escribe nombre o CI...">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                </div>

                @if(!empty($estudiantesEncontrados))
                    <div class="absolute w-full bg-white shadow-2xl border mt-1 rounded-lg overflow-hidden animate-fade-in-down">
                        @foreach($estudiantesEncontrados as $est)
                            <div wire:click="seleccionarEstudiante({{ $est->id_estudiante }})"
                                class="p-4 hover:bg-blue-50 cursor-pointer border-b last:border-0 flex justify-between items-center transition-colors">
                                <div>
                                    <span class="font-bold text-gray-800 text-lg">{{ $est->nombre }} {{ $est->apellido }}</span><br>
                                    <span class="text-sm text-gray-500 font-mono">CI: {{ $est->ci }}</span>
                                </div>
                                <i class="fas fa-chevron-right text-blue-400"></i>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Mensaje Inicial --}}
            <div class="text-center py-20 opacity-50">
                <i class="fas fa-box-open text-7xl text-gray-300 mb-5"></i>
                <h2 class="text-3xl font-black text-gray-400">Historial de Compras</h2>
                <p class="text-lg mt-2 font-bold text-gray-400">Busca a un estudiante para ver todos los artículos, uniformes e insumos que ha adquirido.</p>
            </div>
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- 2. REPORTE DEL ESTUDIANTE (Web e Impresión)--}}
    {{-- ========================================== --}}
    @if($estudianteSeleccionado)
        
        {{-- Tarjeta de Estudiante Seleccionado (Modo Pantalla) --}}
        <div class="print:hidden bg-blue-50 border-l-4 border-blue-600 p-5 mb-6 rounded-xl shadow-sm flex justify-between items-center animate-fade-in-down">
            <div>
                <h3 class="text-2xl font-black text-blue-900">
                    {{ $estudianteSeleccionado->nombre }} {{ $estudianteSeleccionado->apellido }}
                </h3>
                <p class="text-blue-700 text-sm font-bold mt-1"><i class="fa-solid fa-id-card mr-1"></i> CI: {{ $estudianteSeleccionado->ci }}</p>
            </div>
            <button wire:click="limpiarEstudiante" class="bg-white border border-blue-200 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-lg font-bold transition shadow-sm">
                <i class="fas fa-undo"></i> Cambiar Alumno
            </button>
        </div>

        {{-- Filtros del Reporte (Modo Pantalla) --}}
        <div class="print:hidden bg-white p-5 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row justify-between items-end gap-4 z-20 relative animate-fade-in-down">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full md:w-3/4">
                <div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide block mb-1">Desde:</span>
                    <input type="date" wire:model.live="fecha_inicio" class="w-full border border-gray-200 rounded-lg p-2 text-sm text-gray-700 font-bold focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide block mb-1">Hasta:</span>
                    <input type="date" wire:model.live="fecha_fin" class="w-full border border-gray-200 rounded-lg p-2 text-sm text-gray-700 font-bold focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide block mb-1">Filtrar Artículo:</span>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="searchArticulo" placeholder="Ej: Libro..." class="w-full pl-8 border border-gray-200 rounded-lg p-2 text-sm text-gray-700 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-colors">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
            
            <button onclick="window.print()" class="w-full md:w-auto bg-gray-800 text-white px-6 py-2.5 rounded-lg shadow-md hover:bg-black transition-colors flex items-center justify-center gap-2 font-bold h-[42px]">
                <i class="fas fa-print"></i> Imprimir Reporte
            </button>
        </div>

        {{-- Resumen Gráfico (Modo Pantalla) --}}
        <div class="print:hidden grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 animate-fade-in-down">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden flex items-center justify-between">
                <div class="absolute left-0 top-0 w-1.5 h-full bg-orange-500"></div>
                <div>
                    <h3 class="text-gray-400 font-bold mb-1 uppercase text-xs tracking-wider">Unidades Compradas</h3>
                    <div class="text-3xl font-black text-gray-800">{{ $totalArticulos }} <span class="text-sm text-gray-400">ítems</span></div>
                </div>
                <i class="fa-solid fa-box-open text-4xl text-orange-100"></i>
            </div>
            
            <div class="bg-blue-600 rounded-xl shadow-md p-6 text-white transform hover:scale-[1.02] transition-transform flex items-center justify-between">
                <div>
                    <h3 class="text-blue-200 font-bold mb-1 uppercase text-xs tracking-wider">Total Gastado</h3>
                    <div class="text-4xl font-black">{{ number_format($totalRecaudado, 2) }} <span class="text-lg text-blue-300">Bs</span></div>
                </div>
                <i class="fa-solid fa-sack-dollar text-5xl text-blue-500 opacity-50"></i>
            </div>
        </div>

        {{-- ENCABEZADO FORMAL DE IMPRESIÓN --}}
        <div class="hidden print:block mb-8">
            <div class="flex justify-between items-center mb-6 border-b-2 border-black pb-4">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('img/LOGO_POTOSI_01.png') }}" alt="Logo IGLA" class="max-h-20 object-contain grayscale" style="filter: grayscale(100%);">
                    <div class="text-sm">
                        <p class="font-black text-xl tracking-widest uppercase">IGLA POTOSÍ</p>
                        <p class="text-gray-700">Dirección: Calle Tarija #30 - Zona Central</p>
                        <p class="font-bold text-gray-900">POTOSÍ - BOLIVIA</p>
                    </div>
                </div>
                <div class="text-right text-sm">
                    <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Rango:</span> <strong>{{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}</strong> al <strong>{{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</strong></p>
                    <p class="mt-1"><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Impreso por:</span> <strong class="uppercase">{{ auth()->user()->nombre ?? 'Administrador' }}</strong></p>
                </div>
            </div>

            <h1 class="text-center text-2xl font-black tracking-widest uppercase mb-2">KARDEX DE COMPRAS / ADQUISICIONES</h1>
            <div class="text-center mb-6">
                <p class="font-bold text-lg text-gray-800">Estudiante: {{ $estudianteSeleccionado->nombre }} {{ $estudianteSeleccionado->apellido }}</p>
                <p class="text-sm text-gray-600 font-bold">CI: {{ $estudianteSeleccionado->ci }}</p>
            </div>

            <div class="flex justify-center mb-8 mt-2">
                <div class="w-full md:w-2/3 border-2 border-gray-800 rounded-lg p-3 bg-gray-50 flex justify-around items-center text-sm">
                    <div class="text-center">
                        <p class="text-gray-600 font-bold uppercase mb-1">Unidades Totales:</p>
                        <p class="text-xl font-black">{{ $totalArticulos }} ítems</p>
                    </div>
                    <div class="text-center border-l-2 border-gray-300 pl-8">
                        <p class="text-blue-700 font-bold uppercase mb-1">Total Gastado Bs:</p>
                        <p class="text-2xl font-black text-blue-700">{{ number_format($totalRecaudado, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA ADAPTATIVA (Web e Impresión) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden print:rounded-none print:shadow-none print:border-none print:overflow-visible mb-8 animate-fade-in-down">
            <div class="overflow-x-auto print:overflow-visible">
                <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                    <thead class="bg-gray-50 print:bg-transparent">
                        <tr class="print:border-b-2 print:border-black">
                            <th class="px-6 py-3 print:px-2 print:py-1.5 font-bold text-gray-500 print:text-black uppercase text-xs">Fecha / Venta</th>
                            <th class="px-6 py-3 print:px-2 print:py-1.5 font-bold text-gray-500 print:text-black uppercase text-xs">Categoría</th>
                            <th class="px-6 py-3 print:px-2 print:py-1.5 font-bold text-gray-500 print:text-black uppercase text-xs">Artículo</th>
                            <th class="px-6 py-3 print:px-2 print:py-1.5 text-center font-bold text-gray-500 print:text-black uppercase text-xs">Cant.</th>
                            <th class="px-6 py-3 print:px-2 print:py-1.5 text-right font-bold text-gray-500 print:text-black uppercase text-xs">P. Unit</th>
                            <th class="px-6 py-3 print:px-2 print:py-1.5 text-right font-bold text-gray-500 print:text-black uppercase text-xs">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 print:divide-dashed print:divide-gray-300 bg-white">
                        @forelse($listaAdquisiciones as $detalle)
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="px-6 py-3 print:px-2 print:py-1.5 align-top">
                                    <span class="font-bold text-gray-800 print:text-black block">{{ \Carbon\Carbon::parse($detalle->venta->fecha_venta)->format('d/m/Y') }}</span>
                                    <span class="text-[10px] text-gray-400">Venta #{{ $detalle->id_venta }}</span>
                                </td>
                                <td class="px-6 py-3 print:px-2 print:py-1.5 align-top text-gray-600 print:text-black">
                                    {{ $detalle->articulo->categoria->nombre ?? 'General' }}
                                </td>
                                <td class="px-6 py-3 print:px-2 print:py-1.5 align-top">
                                    <span class="font-bold text-blue-700 print:text-black block">{{ $detalle->articulo->nombre }}</span>
                                </td>
                                <td class="px-6 py-3 print:px-2 print:py-1.5 text-center align-top font-black text-gray-700 print:text-black">
                                    {{ $detalle->cantidad }}
                                </td>
                                <td class="px-6 py-3 print:px-2 print:py-1.5 text-right align-top text-gray-600 print:text-black">
                                    {{ number_format($detalle->precio_unitario, 2) }}
                                </td>
                                <td class="px-6 py-3 print:px-2 print:py-1.5 text-right align-top font-black text-gray-900 print:text-black">
                                    {{ number_format($detalle->subtotal, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 print:py-4 text-center text-gray-400 print:text-black italic">
                                    <i class="fa-solid fa-folder-open text-4xl mb-3 text-gray-300 print:hidden"></i>
                                    <p>Este estudiante no tiene compras registradas en este periodo.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- FIRMAS (Solo Impresión) --}}
        <div class="hidden print:flex justify-around mt-20 text-center text-sm pt-8">
            <div class="w-1/3">
                <hr class="border-black mb-1">
                <p>Generado Por</p>
                <p class="text-xs text-gray-500 uppercase">{{ auth()->user()->nombre ?? 'Administrador' }}</p>
            </div>
        </div>

    @endif

    {{-- ESTILOS BASE PARA IMPRESIÓN --}}
    <style>
        @media print {
            @page { margin: 0mm; size: letter portrait; }
            nav, aside, header, .sidebar, .navbar { display: none !important; }
            main, .main-content, #app, body { margin: 0 !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; background: white !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</div>