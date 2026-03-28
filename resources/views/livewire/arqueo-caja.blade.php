<div class="container mx-auto px-4 py-6 print:p-8">

    {{-- ========================================== --}}
    {{-- 1. ENCABEZADOS Y FILTROS WEB (Solo Pantalla) --}}
    {{-- ========================================== --}}
    <div class="print:hidden">
        {{-- <h2 class="text-center text-3xl font-bold mb-6 text-gray-800 border-b pb-4">
            REPORTE Y ARQUEO DE CAJA
        </h2> --}}

        <div class="mb-6 border-l-4 border-orange-500 pl-4">
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">REPORTE Y ARQUEO DE CAJA</h2>
            <p class="text-sm text-gray-500 mt-1">Consulta el arqueo de caja con el detalle completo de ingresos, egresos y saldos del día.</p>
        </div>

        {{-- Filtro y Controles --}}
        <div class="bg-white p-4 rounded-lg shadow-md mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <label class="font-bold text-gray-700">Seleccionar Fecha:</label>
                <input type="date" wire:model.live="fecha_filtro" class="border border-gray-300 rounded p-2 focus:ring-2 focus:ring-blue-500 font-bold text-gray-700">
            </div>
            
            <button onclick="window.print()" class="w-full md:w-auto bg-gray-800 text-white px-6 py-2 rounded-lg shadow-md hover:bg-black transition flex items-center justify-center gap-2 font-bold">
                <i class="fas fa-print"></i> Imprimir Arqueo
            </button>
        </div>

        {{-- Tarjetas de Resumen Visual --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                <h3 class="text-green-100 font-bold mb-1 uppercase text-sm">Saldo en Caja Física (Billetes)</h3>
                <div class="text-4xl font-black mb-2">{{ number_format($saldoCajaFisica, 2) }} Bs</div>
                <div class="text-xs font-bold bg-black bg-opacity-20 inline-block px-2 py-1 rounded">
                    Ingresos: +{{ number_format($ingresosEfectivo, 2) }} | Egresos: -{{ number_format($egresosEfectivo, 2) }}
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                <h3 class="text-blue-100 font-bold mb-1 uppercase text-sm">Saldo en Banco (QR/Transf)</h3>
                <div class="text-4xl font-black mb-2">{{ number_format($saldoBanco, 2) }} Bs</div>
                <div class="text-xs font-bold bg-black bg-opacity-20 inline-block px-2 py-1 rounded">
                    Ingresos: +{{ number_format($ingresosBanco, 2) }} | Egresos: -{{ number_format($egresosBanco, 2) }}
                </div>
            </div>
            <div class="bg-white border-2 border-gray-800 rounded-xl shadow-lg p-6 text-gray-800">
                <h3 class="text-gray-500 font-bold mb-1 uppercase text-sm">Total Movimiento del Día</h3>
                <div class="text-4xl font-black mb-2">{{ number_format($totalGeneral, 2) }} Bs</div>
                <div class="text-sm font-bold text-gray-400">Suma de Caja + Banco</div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 2. ENCABEZADO DE IMPRESIÓN (Solo Impresora) --}}
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
                <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Fecha de Arqueo:</span> <strong class="text-base">{{ \Carbon\Carbon::parse($fecha_filtro)->format('d/m/Y') }}</strong></p>
                <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Impreso el:</span> <strong>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</strong></p>
                <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Usuario / Cajero:</span> <strong class="uppercase">{{ auth()->user()->nombre ?? 'Administrador' }}</strong></p>
            </div>
        </div>
        <h1 class="text-center text-2xl font-black tracking-widest uppercase mb-8">ARQUEO DE CAJA DIARIO</h1>
    </div>

    {{-- ========================================== --}}
    {{-- 3. SECCIONES UNIFICADAS (Web e Impresión)  --}}
    {{-- ========================================== --}}
    
    {{-- 3.1 Resumen de Saldos --}}
    <div class="mb-8 bg-white print:bg-transparent rounded-xl shadow-sm print:shadow-none border border-gray-100 print:border-none p-5 print:p-0">
        <h3 class="font-bold border-b border-gray-400 mb-4 uppercase text-sm bg-gray-50 print:bg-gray-100 p-2 rounded print:rounded-none">1. Resumen de Saldos</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm px-2">
            <div class="bg-gray-50 print:bg-transparent p-4 print:p-0 rounded-lg">
                <p class="flex justify-between mb-1"><span class="font-bold text-gray-600">Total Ingresos Efectivo:</span> <span class="font-mono">{{ number_format($ingresosEfectivo, 2) }} Bs</span></p>
                <p class="flex justify-between mb-2 text-red-600"><span class="font-bold">Total Egresos Efectivo:</span> <span class="font-mono">- {{ number_format($egresosEfectivo, 2) }} Bs</span></p>
                <p class="flex justify-between font-black text-base mt-2 border-t-2 border-gray-800 pt-2 text-green-700"><span>SALDO CAJA FÍSICA:</span> <span>{{ number_format($saldoCajaFisica, 2) }} Bs</span></p>
            </div>
            <div class="bg-gray-50 print:bg-transparent p-4 print:p-0 rounded-lg">
                <p class="flex justify-between mb-1"><span class="font-bold text-gray-600">Total Ingresos Banco (QR):</span> <span class="font-mono">{{ number_format($ingresosBanco, 2) }} Bs</span></p>
                <p class="flex justify-between mb-2 text-red-600"><span class="font-bold">Total Egresos Banco:</span> <span class="font-mono">- {{ number_format($egresosBanco, 2) }} Bs</span></p>
                <p class="flex justify-between font-black text-base mt-2 border-t-2 border-gray-800 pt-2 text-blue-700"><span>SALDO BANCO:</span> <span>{{ number_format($saldoBanco, 2) }} Bs</span></p>
            </div>
        </div>
    </div>

    {{-- 3.2 Detalle de Ingresos --}}
    <div class="mb-8 bg-white print:bg-transparent rounded-xl shadow-sm print:shadow-none border border-gray-100 print:border-none p-5 print:p-0 overflow-hidden">
        <h3 class="font-bold border-b border-gray-400 mb-2 uppercase text-sm bg-gray-50 print:bg-gray-100 p-2 rounded print:rounded-none">2. Detalle de Ingresos</h3>
        <div class="overflow-x-auto print:overflow-visible">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                <thead class="bg-white print:bg-transparent">
                    <tr class="border-b print:border-black">
                        <th class="py-2 print:py-1 w-16 font-bold text-gray-500 print:text-black">Hora</th>
                        <th class="py-2 print:py-1 w-32 font-bold text-gray-500 print:text-black">Módulo Origen</th>
                        <th class="py-2 print:py-1 font-bold text-gray-500 print:text-black">Descripción / Concepto</th>
                        <th class="py-2 print:py-1 font-bold text-gray-500 print:text-black">Método</th>
                        <th class="py-2 print:py-1 text-right font-bold text-gray-500 print:text-black">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 print:divide-dashed print:divide-gray-300">
                    @forelse($listaIngresos as $ingreso)
                        <tr class="hover:bg-green-50 transition-colors">
                            <td class="py-2 print:py-1.5 align-top text-gray-600 print:text-black">{{ \Carbon\Carbon::parse($ingreso->fecha_transaccion)->format('H:i') }}</td>
                            <td class="py-2 print:py-1.5 align-top">
                                @if(str_contains($ingreso->pago->origen_type ?? '', 'Venta'))
                                    <span class="bg-gray-100 print:bg-transparent border border-gray-300 print:border-none text-gray-700 print:text-black px-1.5 py-0.5 print:p-0 rounded text-[9px] print:text-xs font-black uppercase tracking-wider">Tienda POS</span>
                                @elseif(str_contains($ingreso->pago->origen_type ?? '', 'OtrosIngresos'))
                                    <span class="bg-green-50 print:bg-transparent border border-green-200 print:border-none text-green-700 print:text-black px-1.5 py-0.5 print:p-0 rounded text-[9px] print:text-xs font-black uppercase tracking-wider">Extra / Otros</span>
                                @else
                                    <span class="bg-blue-50 print:bg-transparent border border-blue-200 print:border-none text-blue-700 print:text-black px-1.5 py-0.5 print:p-0 rounded text-[9px] print:text-xs font-black uppercase tracking-wider">Académico</span>
                                @endif
                            </td>
                            <td class="py-2 print:py-1.5 align-top">
                                <div class="font-bold text-gray-800 print:text-black">
                                    {{ $ingreso->pago->descripcion ?? 'Ingreso Directo' }}
                                </div>
                                
                                {{-- MAGIA: Si es una Venta de POS, listamos lo que llevó --}}
                                @if(str_contains($ingreso->pago->origen_type ?? '', 'Venta') && $ingreso->pago->origen)
                                    <div class="text-[10px] text-gray-500 print:text-gray-700 mt-1 leading-tight border-l-2 border-gray-200 print:border-gray-400 pl-1.5 ml-1">
                                        @foreach($ingreso->pago->origen->detalles as $detalle)
                                            <div class="mb-0.5">
                                                <span class="font-black">{{ $detalle->cantidad }}x</span> 
                                                <span>{{ $detalle->articulo->nombre ?? 'Artículo' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-2 print:py-1.5 align-top">{{ $ingreso->metodo->nombre }}</td>
                            <td class="py-2 print:py-1.5 text-right align-top font-bold text-green-600 print:text-black">{{ number_format($ingreso->monto, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 print:py-2 text-center text-gray-500 italic">No hubo ingresos en esta fecha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3.3 Detalle de Egresos --}}
    <div class="mb-12 bg-white print:bg-transparent rounded-xl shadow-sm print:shadow-none border border-gray-100 print:border-none p-5 print:p-0 overflow-hidden">
        <h3 class="font-bold border-b border-gray-400 mb-2 uppercase text-sm bg-gray-50 print:bg-gray-100 p-2 rounded print:rounded-none">3. Detalle de Egresos (Salidas)</h3>
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-sm text-left">
                <thead class="bg-white print:bg-transparent">
                    <tr class="border-b print:border-black">
                        <th class="py-2 print:py-1 w-16 font-bold text-gray-500 print:text-black">Hora</th>
                        <th class="py-2 print:py-1 font-bold text-gray-500 print:text-black">Concepto</th>
                        <th class="py-2 print:py-1 font-bold text-gray-500 print:text-black">Proveedor / Doc.</th>
                        <th class="py-2 print:py-1 font-bold text-gray-500 print:text-black">Método</th>
                        <th class="py-2 print:py-1 text-right font-bold text-gray-500 print:text-black">Monto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 print:divide-dashed print:divide-gray-300">
                    @forelse($listaEgresos as $egreso)
                        <tr class="hover:bg-red-50 transition-colors">
                            <td class="py-2 print:py-1.5 align-top text-gray-600 print:text-black">{{ \Carbon\Carbon::parse($egreso->fecha_egreso)->format('H:i') }}</td>
                            <td class="py-2 print:py-1.5 align-top font-bold text-gray-800 print:text-black">{{ $egreso->concepto }}</td>
                            <td class="py-2 print:py-1.5 align-top text-xs">
                                <div class="font-bold">{{ $egreso->proveedor->nombre_empresa ?? 'S/P' }}</div>
                                @if($egreso->nro_factura)
                                    <div class="text-[10px] text-gray-500 print:text-gray-800 uppercase mt-0.5">{{ $egreso->tipo_comprobante }}: {{ $egreso->nro_factura }}</div>
                                @endif
                            </td>
                            <td class="py-2 print:py-1.5 align-top">{{ $egreso->metodoPago->nombre }}</td>
                            <td class="py-2 print:py-1.5 text-right align-top font-bold text-red-600 print:text-black">-{{ number_format($egreso->monto, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 print:py-2 text-center text-gray-500 italic">No hubieron egresos en esta fecha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 4. FIRMAS (Solo Impresora)                 --}}
    {{-- ========================================== --}}
    <div class="hidden print:flex justify-around mt-16 text-center text-sm pt-8">
        <div class="w-1/3">
            <hr class="border-black mb-1">
            <p>Entregué Conforme</p>
            <p class="text-xs text-gray-500">(Firma Cajero/a)</p>
        </div>
        <div class="w-1/3">
            <hr class="border-black mb-1">
            <p>Recibí Conforme</p>
            <p class="text-xs text-gray-500">(Firma Administrador/a)</p>
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