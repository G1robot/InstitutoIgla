<div class="px-4 pb-8">
    
    {{-- SISTEMA WEB (SE OCULTA AL IMPRIMIR) --}}
    <div class="ocultar-al-imprimir">
        
        <div class="mb-8 border-l-4 border-orange-500 pl-4">
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">CONTROL DE MENSUALIDADES</h2>
            <p class="text-sm text-gray-500 mt-1">Gestiona los pagos de las cuotas de los estudiantes inscritos.</p>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar estudiante, plan o CI..." 
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-white text-sm shadow-sm">
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Estudiante</th>
                            <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Plan Académico</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Gestión</th>
                            <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($inscripciones as $ins)
                            <tr wire:key="inscripcion-{{ $ins->id_inscripcion }}" class="hover:bg-blue-50 transition-colors group">
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900 flex items-center gap-2 text-base">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                            <i class="fa-solid fa-user-graduate text-xs"></i>
                                        </div>
                                        <div>
                                            {{ $ins->estudiante->nombre }} {{ $ins->estudiante->apellido }}
                                            <span class="block text-xs text-gray-500 font-mono font-normal">CI: {{ $ins->estudiante->ci }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-md text-xs font-bold border border-gray-200">
                                        <i class="fa-solid fa-layer-group text-blue-400 mr-1"></i> {{ $ins->plan->nombre }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-700">
                                    {{ $ins->gestion_inicio }}
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-right flex justify-end gap-2">
                                    {{-- Botón de Imprimir Extracto Completo --}}
                                    <button wire:click="prepararExtracto({{ $ins->id_inscripcion }})"
                                        class="bg-white border border-gray-300 text-gray-700 hover:text-orange-600 hover:border-orange-300 hover:bg-orange-50 px-3 py-1.5 rounded-lg shadow-sm transition font-bold text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-print"></i> Extracto
                                    </button>

                                    {{-- Botón para ver y cobrar Pagos --}}
                                    <button wire:click="verPagos({{ $ins->id_inscripcion }})"
                                        class="bg-orange-600 text-white hover:bg-blue-700 px-4 py-1.5 rounded-lg shadow-sm transition font-bold text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-cash-register"></i> Cuotas
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fa-solid fa-users-slash text-4xl mb-3 text-gray-300"></i>
                                    <p>No se encontraron estudiantes activos con esa búsqueda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($inscripciones, 'hasPages') && $inscripciones->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    {{ $inscripciones->links() }}
                </div>
            @endif
        </div>
    </div>

    @if($showModal)
        @include('livewire.pagos.inscripcionesPagosModal')
    @endif

    {{-- ========================================== --}}
    {{-- DISEÑO DE IMPRESIÓN 1: RECIBO DE PAGO      --}}
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
            <h2 class="font-bold text-lg uppercase tracking-wide">Comprobante de Pago</h2>
            <p class="text-sm">Nro: <span class="font-bold text-lg">{{ $datosRecibo['nro_recibo'] }}</span></p>
        </div>

        {{-- 3. Datos a los extremos (Izquierda: Estudiante | Derecha: Cajero) --}}
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

        {{-- 4. Detalle Académico --}}
        <div class="mb-4 text-sm px-2 flex justify-between items-center">
            <p class="mb-0">
            <span class="font-bold uppercase text-[10px] text-gray-500 mr-2">Plan:</span>
            {{ $datosRecibo['plan'] }}
            </p>
            <p class="mb-0">
            <span class="font-bold uppercase text-[10px] text-gray-500 mr-2">Cuota a pagar:</span>
            <strong class="text-base border-b border-gray-800">{{ $datosRecibo['cuota'] }}</strong>
            </p>
        </div>

        {{-- 5. Tabla de Importes --}}
        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="border-b-2 border-gray-800">
                    <th class="text-left py-1">Detalle de Transacción</th>
                    <th class="text-right py-1">Importe</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-dashed border-gray-200">
                    <td class="py-2 pr-2 align-top">
                        <div class="font-bold text-base leading-tight">Abono a cuota</div>
                        <div class="text-xs text-gray-500 mt-0.5">Vía: {{ $datosRecibo['metodos'] }}</div>
                    </td>
                    <td class="py-2 text-right font-mono font-bold align-top text-lg">{{ number_format($datosRecibo['monto_pagado'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- 6. Totales --}}
        <div class="flex justify-end mb-6">
            <div class="w-3/4 sm:w-1/2 text-sm">
                <div class="flex justify-between font-black text-lg border-t-2 border-gray-800 pt-1">
                    <span>TOTAL ABONADO Bs:</span>
                    <span>{{ number_format($datosRecibo['monto_pagado'], 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600 mt-1">
                    <span>Efectivo/Ingresado:</span>
                    <span>{{ number_format($datosRecibo['ingresado'] ?? $datosRecibo['monto_pagado'], 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Cambio:</span>
                    <span>{{ number_format($datosRecibo['cambio'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- 7. Pie de página --}}
        <div class="text-center text-[11px] text-gray-500 border-t border-gray-300 pt-3">
            <p>Conserve este comprobante para cualquier reclamo.</p>
            <p class="font-bold text-gray-800 mt-0.5">¡Gracias por su pago!</p>
        </div>
    </div>
    @endif

    {{-- ========================================== --}}
    {{-- DISEÑO DE IMPRESIÓN 2: EXTRACTO COMPLETO   --}}
    {{-- ========================================== --}}
    @if($datosExtracto)
        <div class="zona-impresion-extracto bg-white text-black">
            <div class="text-center border-b-2 border-black pb-4 mb-6">
                <h1 class="text-2xl font-black uppercase tracking-widest">IGLA POTOSÍ</h1>
                <h2 class="text-xl font-bold mt-1">EXTRACTO DE PLAN DE PAGOS</h2>
                <p class="text-sm mt-1">Fecha de emisión: {{ $datosExtracto['fecha_emision'] }}</p>
            </div>

            <div class="mb-6 flex justify-between border border-gray-400 p-4 rounded bg-gray-50">
                <div>
                    <p class="mb-1"><span class="font-bold">Estudiante:</span> {{ $datosExtracto['estudiante'] }}</p>
                    <p><span class="font-bold">Carnet:</span> {{ $datosExtracto['ci'] }}</p>
                </div>
                <div class="text-right">
                    <p class="mb-1"><span class="font-bold">Plan Académico:</span> {{ $datosExtracto['plan'] }}</p>
                    <p><span class="font-bold">Gestión de Inicio:</span> {{ $datosExtracto['gestion'] }}</p>
                </div>
            </div>

            <table class="w-full text-sm border-collapse border border-gray-800 mb-8">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border border-gray-800 p-2 text-left">Concepto / Cuota</th>
                        <th class="border border-gray-800 p-2 text-center">Vencimiento</th>
                        <th class="border border-gray-800 p-2 text-center">Estado</th>
                        <th class="border border-gray-800 p-2 text-right">Abonado (Bs)</th>
                        <th class="border border-gray-800 p-2 text-right">Costo Total (Bs)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datosExtracto['pagos'] as $p)
                        <tr>
                            <td class="border border-gray-800 p-2">{{ $p->descripcion }}</td>
                            <td class="border border-gray-800 p-2 text-center">{{ \Carbon\Carbon::parse($p->fecha_vencimiento)->format('d/m/Y') }}</td>
                            <td class="border border-gray-800 p-2 text-center font-bold uppercase">{{ $p->estado }}</td>
                            <td class="border border-gray-800 p-2 text-right">{{ number_format($p->monto_abonado, 2) }}</td>
                            <td class="border border-gray-800 p-2 text-right">{{ number_format($p->monto_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-black bg-gray-100">
                        <td colspan="3" class="border border-gray-800 p-2 text-right">TOTALES ACUMULADOS:</td>
                        <td class="border border-gray-800 p-2 text-right text-green-700">{{ number_format($datosExtracto['total_pagado'], 2) }} Bs</td>
                        <td class="border border-gray-800 p-2 text-right">{{ number_format($datosExtracto['total_plan'], 2) }} Bs</td>
                    </tr>
                </tfoot>
            </table>

            <div class="text-right text-lg mt-4">
                <span class="font-bold">SALDO PENDIENTE A PAGAR:</span> 
                <span class="font-black text-red-600">{{ number_format($datosExtracto['total_deuda'], 2) }} Bs</span>
            </div>
            
            <div class="mt-16 border-t border-gray-400 pt-4 text-center text-xs text-gray-500">
                Documento emitido por el sistema informático. Este extracto es meramente informativo.
            </div>
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- SCRIPT PARA AUTO-IMPRIMIR EL EXTRACTO      --}}
    {{-- ========================================== --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('abrir-impresion-extracto', () => {
                setTimeout(() => { window.print(); }, 300);
            });
        });
    </script>

    {{-- ========================================== --}}
    {{-- CSS MÁGICO PARA AMBOS TIPOS DE IMPRESIÓN   --}}
    {{-- ========================================== --}}
    <style>
        .zona-impresion, .zona-impresion-extracto { display: none; }

        @media print {
            nav, aside, .ocultar-al-imprimir, .no-imprimir { display: none !important; }
            
            @page { margin: 0 !important; size: auto; }
            body, html { margin: 0 !important; padding: 0 !important; background-color: white !important; }
            
            main, main > div, .container, .px-4 { 
                margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important;
                border-radius: 0 !important; background: white !important; max-width: 100% !important;
            }

            /* Habilitar Flexbox */
            .zona-impresion .flex, .zona-impresion-extracto .flex { display: flex !important; }

            /* FORMATO 1: RECIBO DE PAGO (MITAD DE HOJA) */
            .zona-impresion {
                display: block !important; position: absolute !important; top: 0 !important; left: 0 !important;
                width: 100% !important; 
                padding: 1cm 1.5cm !important; 
                border: none !important; box-shadow: none !important;
                /* LA MAGIA: Fondo blanco sólido y capa superior para tapar la vista web */
                background: white !important; 
                z-index: 9999 !important; 
                border-radius: 0 !important; color: black !important;
            }
            .zona-impresion * { color: black !important; font-family: Arial, Helvetica, sans-serif !important; background: transparent !important; }
            .zona-impresion p, .zona-impresion td, .zona-impresion th, .zona-impresion span, .zona-impresion div { font-size: 11pt !important; line-height: 1.3 !important; }
            .zona-impresion h1 { font-size: 16pt !important; margin-bottom: 2px !important; }
            .zona-impresion h2 { font-size: 13pt !important; margin-bottom: 0 !important; text-transform: uppercase !important; }
            .zona-impresion table { width: 100% !important; table-layout: auto !important; border-collapse: collapse !important; border: none !important; }
            .zona-impresion th, .zona-impresion td { border: none !important; border-bottom: 1px dashed #ccc !important; padding: 4px 0 !important; }
            .zona-impresion thead th { border-bottom: 2px solid black !important; }

            /* FORMATO 2: EXTRACTO DE PLAN DE PAGOS */
            .zona-impresion-extracto {
                display: block !important; position: absolute !important; top: 0 !important; left: 0 !important;
                width: 100% !important; padding: 1.5cm 2cm !important; color: black !important; background: white !important;
            }
            .zona-impresion-extracto * { color: black !important; font-family: Arial, Helvetica, sans-serif !important; background: transparent !important; }
            .zona-impresion-extracto table { width: 100% !important; border-collapse: collapse !important; margin-top: 15px !important; }
            .zona-impresion-extracto th, .zona-impresion-extracto td { border: 1px solid #000 !important; padding: 8px !important; font-size: 11pt !important; }
            .zona-impresion-extracto th { background-color: #f3f4f6 !important; font-weight: bold !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
    
</div>

