<div class="container mx-auto px-4 py-6 print:p-8">

    {{-- ========================================== --}}
    {{-- 1. ENCABEZADOS Y FILTROS WEB (Se ocultan al imprimir) --}}
    {{-- ========================================== --}}
    <div class="print:hidden">
        <div class="mb-6 border-l-4 border-red-500 pl-4">
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">REPORTE DETALLADO DE EGRESOS</h2>
            <p class="text-sm text-gray-500 mt-1">Consulta y exporta todas las salidas de dinero y gastos en un rango de fechas.</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex flex-col md:flex-row items-center gap-4 w-full md:w-auto">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-gray-500 uppercase tracking-wide">Desde:</span>
                    <input type="date" wire:model.live="fecha_inicio" class="border border-gray-200 rounded-lg p-2 text-sm text-gray-700 font-bold focus:ring-red-500 focus:border-red-500">
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-gray-500 uppercase tracking-wide">Hasta:</span>
                    <input type="date" wire:model.live="fecha_fin" class="border border-gray-200 rounded-lg p-2 text-sm text-gray-700 font-bold focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
            
            <button onclick="window.print()" class="w-full md:w-auto bg-gray-800 text-white px-6 py-2.5 rounded-lg shadow-md hover:bg-black transition-colors flex items-center justify-center gap-2 font-bold">
                <i class="fas fa-print"></i> Imprimir Reporte
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-2 h-full bg-red-500"></div>
                <h3 class="text-gray-400 font-bold mb-1 uppercase text-xs tracking-wider">Gastos en Efectivo</h3>
                <div class="text-3xl font-black text-gray-800">{{ number_format($totalEfectivo, 2) }} <span class="text-sm text-gray-400">Bs</span></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                <div class="absolute right-0 top-0 w-2 h-full bg-orange-500"></div>
                <h3 class="text-gray-400 font-bold mb-1 uppercase text-xs tracking-wider">Gastos Banco / QR</h3>
                <div class="text-3xl font-black text-gray-800">{{ number_format($totalBanco, 2) }} <span class="text-sm text-gray-400">Bs</span></div>
            </div>
            <div class="bg-red-600 rounded-xl shadow-md p-6 text-white transform hover:scale-[1.02] transition-transform">
                <h3 class="text-red-100 font-bold mb-1 uppercase text-xs tracking-wider">Total Gastado</h3>
                <div class="text-4xl font-black">{{ number_format($totalGeneral, 2) }} <span class="text-lg text-red-200">Bs</span></div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 2. ENCABEZADO DE IMPRESIÓN (Solo visible al imprimir) --}}
    {{-- ========================================== --}}
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
                <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Rango del Reporte:</span></p>
                <p><strong class="text-base">{{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}</strong> <span class="text-gray-400 mx-1">al</span> <strong class="text-base">{{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</strong></p>
                <p class="mt-1"><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Impreso por:</span> <strong class="uppercase">{{ auth()->user()->nombre ?? 'Administrador' }}</strong></p>
            </div>
        </div>

        <h1 class="text-center text-2xl font-black tracking-widest uppercase mb-8">REPORTE GENERAL DE EGRESOS</h1>

        <div class="flex justify-center mb-8">
            <div class="w-full border-2 border-gray-800 rounded-lg p-4 bg-gray-50 flex justify-between items-center text-sm">
                <div><p class="text-gray-600 font-bold uppercase mb-1">Efectivo:</p><p class="text-xl font-black">{{ number_format($totalEfectivo, 2) }} Bs</p></div>
                <div class="text-center border-l border-r border-gray-300 px-6"><p class="text-gray-600 font-bold uppercase mb-1">Banco / QR:</p><p class="text-xl font-black">{{ number_format($totalBanco, 2) }} Bs</p></div>
                <div class="text-right"><p class="text-red-700 font-bold uppercase mb-1">Total Gastado:</p><p class="text-2xl font-black text-red-700">{{ number_format($totalGeneral, 2) }} Bs</p></div>
            </div>
        </div>
        
        <h3 class="font-bold border-b border-gray-400 mb-2 uppercase text-sm bg-gray-100 p-1">Detalle de Salidas de Dinero</h3>
    </div>

    {{-- ========================================== --}}
    {{-- 3. LA TABLA ÚNICA (Visible en Web e Impresión) --}}
    {{-- ========================================== --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden print:rounded-none print:shadow-none print:border-none print:overflow-visible mb-8">
        <div class="overflow-x-auto print:overflow-visible">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                <thead class="bg-gray-50 print:bg-transparent">
                    <tr class="print:border-b-2 print:border-black">
                        <th class="px-6 py-3 print:px-2 print:py-1.5 font-bold text-gray-500 print:text-black uppercase">Fecha y Hora</th>
                        <th class="px-6 py-3 print:px-2 print:py-1.5 font-bold text-gray-500 print:text-black uppercase">Proveedor / Doc.</th>
                        <th class="px-6 py-3 print:px-2 print:py-1.5 font-bold text-gray-500 print:text-black uppercase">Concepto del Gasto</th>
                        <th class="px-6 py-3 print:px-2 print:py-1.5 font-bold text-gray-500 print:text-black uppercase">Método</th>
                        <th class="px-6 py-3 print:px-2 print:py-1.5 text-right font-bold text-gray-500 print:text-black uppercase">Monto (Bs)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 print:divide-dashed print:divide-gray-300 bg-white">
                    @forelse($listaEgresos as $egreso)
                        <tr class="hover:bg-red-50 transition-colors">
                            <td class="px-6 py-3 print:px-2 print:py-1.5 whitespace-nowrap text-sm text-gray-600 print:text-black align-top">
                                <span class="font-bold">{{ \Carbon\Carbon::parse($egreso->fecha_egreso)->format('d/m/Y') }}</span>
                                <span class="text-xs ml-1">{{ \Carbon\Carbon::parse($egreso->fecha_egreso)->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-3 print:px-2 print:py-1.5 align-top">
                                <div class="font-bold text-gray-800 print:text-black">{{ $egreso->proveedor->nombre_empresa ?? 'S/P' }}</div>
                                @if($egreso->nro_factura)
                                    <div class="text-[10px] bg-gray-100 print:bg-transparent border border-gray-200 print:border-none px-1.5 py-0.5 rounded inline-block mt-1 font-bold text-gray-600 print:text-black uppercase">
                                        {{ $egreso->tipo_comprobante }}: {{ $egreso->nro_factura }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-3 print:px-2 print:py-1.5 text-sm align-top">
                                <div class="font-bold text-gray-900 print:text-black">{{ $egreso->concepto }}</div>
                                @if($egreso->descripcion)
                                    <div class="text-xs text-gray-500 italic print:text-gray-800 mt-0.5">{{ Str::limit($egreso->descripcion, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-3 print:px-2 print:py-1.5 whitespace-nowrap text-sm align-top">
                                {{ $egreso->metodoPago->nombre }}
                            </td>
                            <td class="px-6 py-3 print:px-2 print:py-1.5 whitespace-nowrap text-right text-base font-black text-red-600 print:text-black align-top">
                                -{{ number_format($egreso->monto, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 print:py-4 text-center text-gray-400 print:text-black italic">
                                <i class="fa-solid fa-file-invoice-dollar text-4xl mb-3 text-gray-300 print:hidden"></i>
                                <p>No se encontraron egresos en este rango de fechas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 4. FIRMAS (Solo visible al imprimir)         --}}
    {{-- ========================================== --}}
    <div class="hidden print:flex justify-around mt-20 text-center text-sm pt-8">
        <div class="w-1/3">
            <hr class="border-black mb-1">
            <p>Elaborado Por</p>
            <p class="text-xs text-gray-500 uppercase">{{ auth()->user()->nombre ?? 'Administrador' }}</p>
        </div>
        <div class="w-1/3">
            <hr class="border-black mb-1">
            <p>Revisado / Aprobado Por</p>
            <p class="text-xs text-gray-500">Dirección / Administración</p>
        </div>
    </div>

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