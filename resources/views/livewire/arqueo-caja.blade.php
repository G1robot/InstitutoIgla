<div class="container mx-auto px-4 py-6">

    {{-- ========================================== --}}
    {{-- MODO PANTALLA (NO SE IMPRIME)              --}}
    {{-- ========================================== --}}
    <div class="no-imprimir">
        <h2 class="text-center text-3xl font-bold mb-6 text-gray-800 border-b pb-4">
            REPORTE Y ARQUEO DE CAJA
        </h2>

        {{-- Filtro y Controles --}}
        <div class="bg-white p-4 rounded-lg shadow-md mb-6 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <label class="font-bold text-gray-700">Seleccionar Fecha:</label>
                <input type="date" wire:model.live="fecha_filtro" class="border rounded p-2 focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-2 rounded shadow hover:bg-black transition flex items-center gap-2 font-bold">
                <i class="fas fa-print"></i> Imprimir Reporte
            </button>
        </div>

        {{-- Tarjetas de Resumen Visual --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Caja Física --}}
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <h3 class="text-green-100 font-bold mb-1 uppercase text-sm">Saldo en Caja Físca (Billetes)</h3>
                <div class="text-4xl font-black mb-2">{{ number_format($saldoCajaFisica, 2) }} Bs</div>
                <div class="text-sm">
                    Ingresos: +{{ $ingresosEfectivo }} | Egresos: -{{ $egresosEfectivo }}
                </div>
            </div>

            {{-- Banco --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <h3 class="text-blue-100 font-bold mb-1 uppercase text-sm">Saldo en Banco (QR/Transf)</h3>
                <div class="text-4xl font-black mb-2">{{ number_format($saldoBanco, 2) }} Bs</div>
                <div class="text-sm">
                    Ingresos: +{{ $ingresosBanco }} | Egresos: -{{ $egresosBanco }}
                </div>
            </div>

            {{-- Total General --}}
            <div class="bg-white border-2 border-gray-800 rounded-xl shadow-lg p-6 text-gray-800">
                <h3 class="text-gray-500 font-bold mb-1 uppercase text-sm">Total Movimiento del Día</h3>
                <div class="text-4xl font-black mb-2">{{ number_format($totalGeneral, 2) }} Bs</div>
                <div class="text-sm font-bold text-gray-400">
                    Suma de Caja + Banco
                </div>
            </div>
        </div>
    </div>


    {{-- ========================================== --}}
    {{-- MODO IMPRESIÓN (EL REPORTE FORMAL)         --}}
    {{-- ========================================== --}}
    <div class="zona-impresion bg-white p-8 rounded-lg shadow-sm border">
        
        {{-- Cabecera del Documento --}}
        <div class="flex justify-between items-start mb-6 border-b-2 border-black pb-4">
            <div class="text-sm">
                <p class="font-bold text-lg">IGLA POTOSÍ</p>
                <p>Dirección: Calle Tarija #30 - Zona Central</p>
                <p class="font-bold">POTOSÍ - BOLIVIA</p>
            </div>
            <div class="text-right text-sm">
                <p><strong>Fecha de Arqueo:</strong> {{ \Carbon\Carbon::parse($fecha_filtro)->format('d/m/Y') }}</p>
                <p><strong>Fecha de Impresión:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
                <p><strong>Usuario:</strong> {{ auth()->user()->name ?? 'Administrador' }}</p>
            </div>
        </div>

        <h1 class="text-center text-2xl font-black tracking-widest uppercase mb-8">
            ARQUEO DE CAJA DIARIO
        </h1>

        {{-- Resumen Financiero --}}
        <div class="mb-8">
            <h3 class="font-bold border-b border-gray-400 mb-2 uppercase text-sm bg-gray-100 p-1">1. Resumen de Saldos</h3>
            <div class="grid grid-cols-2 gap-4 text-sm px-2">
                <div>
                    <p class="flex justify-between"><span>Total Ingresos Efectivo:</span> <span>{{ number_format($ingresosEfectivo, 2) }} Bs</span></p>
                    <p class="flex justify-between text-red-600"><span>Total Egresos Efectivo:</span> <span>- {{ number_format($egresosEfectivo, 2) }} Bs</span></p>
                    <p class="flex justify-between font-bold text-base mt-1 border-t border-dashed pt-1"><span>SALDO CAJA FÍSICA:</span> <span>{{ number_format($saldoCajaFisica, 2) }} Bs</span></p>
                </div>
                <div>
                    <p class="flex justify-between"><span>Total Ingresos Banco (QR):</span> <span>{{ number_format($ingresosBanco, 2) }} Bs</span></p>
                    <p class="flex justify-between text-red-600"><span>Total Egresos Banco:</span> <span>- {{ number_format($egresosBanco, 2) }} Bs</span></p>
                    <p class="flex justify-between font-bold text-base mt-1 border-t border-dashed pt-1"><span>SALDO BANCO:</span> <span>{{ number_format($saldoBanco, 2) }} Bs</span></p>
                </div>
            </div>
        </div>

        {{-- Detalle de Ingresos --}}
        <div class="mb-8">
            <h3 class="font-bold border-b border-gray-400 mb-2 uppercase text-sm bg-gray-100 p-1">2. Detalle de Ingresos</h3>
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-1">Hora</th>
                        <th class="py-1">Descripción / Concepto</th>
                        <th class="py-1">Método</th>
                        <th class="py-1 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listaIngresos as $ingreso)
                        <tr class="border-b border-dashed border-gray-200">
                            <td class="py-1">{{ \Carbon\Carbon::parse($ingreso->fecha_transaccion)->format('H:i') }}</td>
                            <td class="py-1">{{ $ingreso->pago->descripcion ?? 'Ingreso' }}</td>
                            <td class="py-1">{{ $ingreso->metodo->nombre }}</td>
                            <td class="py-1 text-right">{{ number_format($ingreso->monto, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-2 text-center text-gray-500 italic">No hubieron ingresos en esta fecha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Detalle de Egresos --}}
        <div class="mb-12">
            <h3 class="font-bold border-b border-gray-400 mb-2 uppercase text-sm bg-gray-100 p-1">3. Detalle de Egresos (Salidas)</h3>
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-1">Hora</th>
                        <th class="py-1">Concepto</th>
                        <th class="py-1">Proveedor / Doc.</th>
                        <th class="py-1">Método</th>
                        <th class="py-1 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listaEgresos as $egreso)
                        <tr class="border-b border-dashed border-gray-200">
                            <td class="py-1">{{ \Carbon\Carbon::parse($egreso->fecha_egreso)->format('H:i') }}</td>
                            <td class="py-1">{{ $egreso->concepto }}</td>
                            <td class="py-1">
                                {{ $egreso->proveedor->nombre_empresa ?? 'S/P' }} 
                                {{ $egreso->nro_factura ? '(Doc: '.$egreso->nro_factura.')' : '' }}
                            </td>
                            <td class="py-1">{{ $egreso->metodoPago->nombre }}</td>
                            <td class="py-1 text-right text-red-600">-{{ number_format($egreso->monto, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-2 text-center text-gray-500 italic">No hubieron egresos en esta fecha.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Firmas --}}
        <div class="flex justify-around mt-16 text-center text-sm pt-8">
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

    </div>

    {{-- ========================================== --}}
    {{-- CSS MÁGICO PARA IMPRESIÓN                  --}}
    {{-- ========================================== --}}
    <style>
        /* 1. Magia para quitar la URL, Fecha y Título que pone el navegador */
        @page {
            margin: 0mm; /* Al poner el margen de la hoja en 0, el navegador ya no tiene dónde imprimir la URL */
            size: letter portrait; /* Opcional: define el tamaño de hoja (A4 o letter) */
        }

        @media print {
            /* 2. Ocultamos VISUALMENTE todo el cuerpo de la página */
            body * {
                visibility: hidden;
            }

            /* 3. Volvemos a mostrar SOLAMENTE nuestra zona de impresión y sus elementos internos */
            .zona-impresion, .zona-impresion * {
                visibility: visible;
            }

            /* 4. Arrancamos nuestra zona de impresión y la pegamos en la esquina superior izquierda absoluta */
            .zona-impresion {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                /* Le damos un poco de margen interno para que no choque con el borde físico del papel */
                padding: 1.5cm !important; 
                border: none !important;
                box-shadow: none !important;
            }

            /* 5. Forzar la impresión de colores de fondo (como los grises de las tablas) */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>

</div>

