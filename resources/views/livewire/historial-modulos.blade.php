<div class="container mx-auto px-6 py-8">
    <div class="ocultar-al-imprimir">

        <h2 class="text-center text-3xl font-bold mb-8 text-gray-800 border-b pb-4">
            HISTORIAL ACADÉMICO Y COBROS
        </h2>

        {{-- 1. BUSCADOR DE ESTUDIANTE --}}
        <div class="max-w-3xl mx-auto mb-10 relative z-30">
            <label class="block text-gray-700 text-sm font-bold mb-2">Buscar Estudiante:</label>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full p-4 pl-12 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 text-lg"
                    placeholder="Escribe nombre o CI...">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
            </div>

            @if(!empty($estudiantesEncontrados))
                <div class="absolute w-full bg-white shadow-2xl border mt-1 rounded-lg overflow-hidden animate-fade-in-down">
                    @foreach($estudiantesEncontrados as $est)
                        <div wire:key="estudiante-{{ $est->id_estudiante }}"
                            wire:click="seleccionarEstudiante({{ $est->id_estudiante }})"
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

        {{-- 2. INFORMACIÓN DEL ESTUDIANTE --}}
        @if($estudianteSeleccionado)
            
            <div class="bg-blue-50 border-l-4 border-blue-600 p-5 mb-8 rounded-xl shadow-sm flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-black text-blue-900">
                        {{ $estudianteSeleccionado->nombre }} {{ $estudianteSeleccionado->apellido }}
                    </h3>
                    <p class="text-blue-700 text-sm font-bold mt-1"><i class="fa-solid fa-id-card mr-1"></i> CI: {{ $estudianteSeleccionado->ci }}</p>
                </div>
                <div class="text-right">
                    <span class="bg-white px-4 py-2 rounded-lg border border-blue-200 text-blue-800 text-sm font-bold shadow-sm inline-block">
                        {{ count($modulos) }} Módulos Inscritos
                    </span>
                </div>
            </div>

            {{-- 3. ESTADO DEL PUP (PAGO ÚNICO PERMANENTE) --}}
            @if($pagoPUP)
                @php $deudaPUP = $pagoPUP->monto_total - $pagoPUP->monto_abonado; @endphp
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-5 mb-8 flex justify-between items-center {{ $pagoPUP->estado == 'pagado' ? 'border-l-4 border-l-green-500' : 'border-l-4 border-l-orange-500' }}">
                    <div>
                        <h4 class="font-black text-gray-800 text-lg"><i class="fa-solid fa-file-invoice-dollar mr-2 text-gray-400"></i>Pago Único Permanente (PUP)</h4>
                        <p class="text-sm text-gray-500 mt-1">Costo Total: {{ number_format($pagoPUP->monto_total, 2) }} Bs | Abonado: {{ number_format($pagoPUP->monto_abonado, 2) }} Bs</p>
                    </div>
                    <div class="text-right">
                        @if($pagoPUP->estado == 'pagado')
                            <span class="bg-green-100 text-green-700 px-4 py-2 rounded-lg font-black text-sm border border-green-200"><i class="fas fa-check-circle"></i> PAGADO</span>
                        @else
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <span class="text-xs font-bold text-gray-400 uppercase block">Deuda Pendiente</span>
                                    <span class="text-xl font-black text-red-500">{{ number_format($deudaPUP, 2) }} Bs</span>
                                </div>
                                <button wire:click="abrirModalPago({{ $pagoPUP->id_pago }})" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-bold shadow-md transition-colors flex items-center gap-2">
                                    <i class="fa-solid fa-hand-holding-dollar"></i> Abonar
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 4. LISTA DE MÓDULOS --}}
            <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2 uppercase tracking-wider">Historial de Módulos</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($modulos as $ins)
                    @php
                        $esFinalizado = $ins->estado === 'finalizado';
                        $pago = $ins->pagos->first(); 
                    @endphp

                    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden relative group hover:shadow-2xl transition duration-300 flex flex-col justify-between">
                        
                        <div class="h-2 w-full {{ $esFinalizado ? 'bg-green-500' : 'bg-blue-500' }}"></div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-xs font-bold px-2 py-1 rounded uppercase tracking-wider {{ $esFinalizado ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $ins->estado }}
                                </span>
                                <span class="text-xs text-gray-400 font-mono">{{ \Carbon\Carbon::parse($ins->fecha_inscripcion)->format('d/m/Y') }}</span>
                            </div>

                            <h4 class="text-lg font-bold text-gray-800 mb-1 leading-tight">{{ $ins->modulo->nombre }}</h4>
                            <p class="text-sm text-gray-500 mb-4 font-bold">Costo Base: {{ number_format($ins->costo_al_momento, 2) }} Bs</p>

                            {{-- Información del Pago y Botón de Abono --}}
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mt-auto">
                                @if($pago && $pago->estado == 'pagado')
                                    <div class="text-center">
                                        <i class="fas fa-check-circle text-3xl text-green-500 mb-2 block"></i>
                                        <span class="text-green-700 font-black text-sm uppercase">Pagado Completo</span>
                                    </div>
                                @elseif($pago)
                                    @php $deudaMod = $pago->monto_total - $pago->monto_abonado; @endphp
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-bold text-gray-500 uppercase">Abonado</span>
                                        <span class="text-sm font-bold text-gray-700">{{ number_format($pago->monto_abonado, 2) }} Bs</span>
                                    </div>
                                    <div class="flex justify-between items-center mb-3 pt-2 border-t border-gray-200">
                                        <span class="text-xs font-black text-red-500 uppercase">Deuda</span>
                                        <span class="text-lg font-black text-red-600">{{ number_format($deudaMod, 2) }} Bs</span>
                                    </div>
                                    <button wire:click="abrirModalPago({{ $pago->id_pago }})" class="w-full bg-orange-100 border border-orange-300 text-orange-700 hover:bg-orange-500 hover:text-white py-2 rounded-lg font-bold transition-colors flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-cash-register"></i> Registrar Abono
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Acciones Académicas --}}
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 text-center">
                            @if(!$esFinalizado)
                                <button wire:click="marcarFinalizado({{ $ins->id_inscripcion_modulo }})" 
                                    class="w-full bg-gray-800 text-white font-bold py-2.5 rounded-lg shadow-md hover:bg-black transition flex items-center justify-center gap-2">
                                    <i class="fas fa-graduation-cap"></i> Aprobar Módulo
                                </button>
                            @else
                                <div class="flex gap-2">
                                    <button class="flex-1 bg-gray-200 text-gray-500 font-bold py-2 rounded-lg cursor-not-allowed border border-gray-300" disabled>
                                        <i class="fas fa-check-double"></i> Módulo Finalizado
                                    </button>
                                    <button wire:click="reactivarModulo({{ $ins->id_inscripcion_modulo }})" class="bg-white border border-gray-300 text-gray-600 px-4 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors" title="Reactivar">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                @empty
                    <div class="col-span-3 text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
                        <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-book-open text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-600">Sin módulos cursados</h3>
                        <p class="text-gray-500">Este estudiante no tiene inscripciones a módulos registradas.</p>
                    </div>
                @endforelse
            </div>

        @elseif($search == '')
            <div class="text-center py-20 opacity-50">
                <i class="fas fa-user-graduate text-7xl text-gray-300 mb-5"></i>
                <h2 class="text-3xl font-black text-gray-400">Seleccione un estudiante</h2>
                <p class="text-lg mt-2 font-bold text-gray-400">Utilice el buscador superior para ver deudas y el historial académico.</p>
            </div>
        @endif

        {{-- ========================================== --}}
        {{-- MODAL DE REGISTRO DE ABONO                 --}}
        {{-- ========================================== --}}
        @if($showModalPago)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm flex items-center justify-center z-50 animate-fade-in-down px-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border-t-4 border-orange-500 flex flex-col max-h-[90vh]">
                
                {{-- Cabecera --}}
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 shrink-0">
                    <h3 class="font-black text-gray-800 text-lg flex items-center gap-2">
                        <i class="fa-solid fa-cash-register text-orange-500"></i> Recibir Pago
                    </h3>
                    <button wire:click="cerrarModalPago" class="text-gray-400 hover:text-gray-700 text-2xl transition">&times;</button>
                </div>
                
                {{-- Cuerpo scrolleable --}}
                <div class="p-6 overflow-y-auto custom-scrollbar">
                    @error('general') 
                        <div class="bg-red-50 text-red-600 p-3 rounded-lg border border-red-200 text-sm font-bold mb-4"><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</div>
                    @enderror

                    <div class="mb-4">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Concepto del Pago</p>
                        <p class="font-bold text-gray-800 text-lg leading-tight">{{ $descripcionPago }}</p>
                    </div>

                    {{-- HISTORIAL DE TRANSACCIONES PREVIAS --}}
                    @if(count($transaccionesPago) > 0)
                        <div class="mb-5">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Historial de Abonos Previos</p>
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-gray-50 text-gray-500 text-[10px] uppercase">
                                        <tr>
                                            <th class="px-3 py-2 font-bold">Fecha / Hora</th>
                                            <th class="px-3 py-2 font-bold">Método</th>
                                            <th class="px-3 py-2 text-right font-bold">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @foreach($transaccionesPago as $tx)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-3 py-2 text-gray-600 text-xs">
                                                    <span class="font-bold">{{ \Carbon\Carbon::parse($tx->fecha_transaccion)->format('d/m/Y') }}</span>
                                                    {{ \Carbon\Carbon::parse($tx->fecha_transaccion)->format('H:i') }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-600 text-xs font-bold">{{ $tx->metodo->nombre }}</td>
                                                <td class="px-3 py-2 text-right font-black text-green-600">+{{ number_format($tx->monto, 2) }} Bs</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Resumen de Deuda --}}
                    <div class="flex justify-between items-center bg-red-50 p-4 rounded-xl border border-red-100 mb-5">
                        <span class="font-bold text-red-800 uppercase text-sm">Deuda Pendiente</span>
                        <span class="font-black text-2xl text-red-600 font-mono">{{ number_format($saldoPendiente, 2) }} <span class="text-sm">Bs</span></span>
                    </div>

                    {{-- FECHA MANUAL --}}
                    <div class="mb-4 bg-orange-50 p-3 rounded-lg border border-orange-200">
                        <label class="block text-[10px] font-black text-orange-800 uppercase mb-1 flex items-center gap-1">
                            <i class="fa-solid fa-calendar-day"></i> Fecha del Pago (Modo Migración)
                        </label>
                        <input type="date" wire:model="fechaPagoManual" 
                            class="w-full px-3 py-1.5 border border-orange-300 rounded text-sm font-bold text-gray-700 focus:ring-orange-500 focus:border-orange-500 bg-white">
                        <p class="text-[9px] text-orange-600 mt-1 leading-tight">Cambia esto solo si estás registrando un recibo antiguo. Por defecto es hoy.</p>
                    </div>

                    {{-- MÉTODOS DE PAGO --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Ingresar Abono (Múltiples métodos permitidos)</label>
                        @foreach($metodosPago as $metodo)
                            <div class="flex shadow-sm rounded-lg overflow-hidden border border-gray-300 focus-within:border-orange-500 focus-within:ring-1 focus-within:ring-orange-500 transition-all bg-white">
                                <span class="inline-flex items-center justify-center px-3 bg-gray-50 text-gray-600 text-xs font-bold w-24 border-r border-gray-200">
                                    {{ $metodo->nombre }}
                                </span>
                                <input type="number" step="0.50" wire:model.live="montosPago.{{ $metodo->id_metodo_pago }}" 
                                    class="flex-1 w-full px-3 py-2 border-none text-sm font-bold text-gray-800 focus:ring-0 bg-white" placeholder="0.00">
                                <button wire:click="llenarSaldo({{ $metodo->id_metodo_pago }})" class="px-3 bg-gray-50 text-orange-500 hover:bg-orange-100 hover:text-orange-700 transition font-bold text-[10px] uppercase border-l border-gray-200" title="Pagar deuda con este método">
                                    Max
                                </button>
                            </div>
                        @endforeach
                    </div>

                    {{-- CAMBIO --}}
                    <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Efectivo Recibido (Billete)</label>
                            <div class="relative">
                                <input type="number" step="0.50" wire:model.live="efectivoRecibido" 
                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm font-bold text-gray-800 focus:ring-orange-500 focus:border-orange-500 bg-gray-50" placeholder="Ej: 100">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-money-bill-wave text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2 text-right border border-gray-200 flex flex-col justify-center">
                            <span class="text-[10px] font-bold text-gray-500 uppercase block">Cambio a devolver</span>
                            <span class="text-xl font-black {{ $cambio > 0 ? 'text-red-500' : 'text-gray-400' }}">
                                {{ number_format($cambio, 2) }} <span class="text-xs">Bs</span>
                            </span>
                        </div>
                    </div>

                    {{-- RESUMEN FINAL --}}
                    <div class="mt-4 flex justify-between items-center bg-gray-800 text-white p-3 rounded-lg shadow-sm">
                        <span class="text-xs font-bold uppercase tracking-wide block">Total Ingresado</span>
                        <span class="text-xl font-black {{ $totalIngresado > $saldoPendiente + 0.1 ? 'text-red-400' : 'text-green-400' }}">
                            {{ number_format($totalIngresado, 2) }} <span class="text-sm text-gray-300">Bs</span>
                        </span>
                    </div>
                    @error('pago') <span class="text-red-500 text-xs font-bold block mt-2 text-center"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> @enderror

                </div>
                
                {{-- Pie del Modal --}}
                <div class="px-6 py-4 bg-white border-t border-gray-100 flex justify-end gap-3 shrink-0">
                    <button wire:click="cerrarModalPago" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-50 border border-gray-300 rounded-xl hover:bg-gray-100 transition">Cancelar</button>
                    <button wire:click="registrarAbono" wire:loading.attr="disabled" class="px-6 py-2.5 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-xl shadow-md flex items-center gap-2 transition disabled:opacity-50" {{ $totalIngresado <= 0 ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="registrarAbono"><i class="fa-solid fa-check"></i> Efectuar Cobro</span>
                        <span wire:loading wire:target="registrarAbono"><i class="fa-solid fa-spinner fa-spin"></i> Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- ========================================== --}}
        {{-- MODAL DE ÉXITO Y DESCARGA                  --}}
        {{-- ========================================== --}}
        @if($showModalExito)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm flex items-center justify-center z-50 animate-fade-in-down px-4">
            <div class="bg-white p-8 rounded-3xl shadow-2xl text-center max-w-sm w-full border-t-4 border-green-500">
                <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                    <i class="fas fa-check-double text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-800 mb-2">¡Pago Recibido!</h3>
                <p class="text-gray-500 mb-8 text-sm font-bold">El abono se ha registrado y el saldo del estudiante ha sido actualizado.</p>
                
                <div class="flex flex-col gap-3">
                    <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-3.5 rounded-xl font-bold hover:bg-black transition shadow-lg flex justify-center items-center gap-2">
                        <i class="fa-solid fa-print"></i> Imprimir Recibo
                    </button>

                    <button wire:click="descargarReciboPdf" wire:loading.attr="disabled" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition w-full flex items-center justify-center gap-2 shadow-lg disabled:opacity-50">
                        <span wire:loading.remove wire:target="descargarReciboPdf"><i class="fa-solid fa-file-pdf"></i> Descargar PDF</span>
                        <span wire:loading wire:target="descargarReciboPdf"><i class="fa-solid fa-spinner fa-spin"></i> Generando PDF...</span>
                    </button>

                    <button wire:click="cerrarModalExito" class="bg-gray-100 text-gray-700 px-6 py-3.5 rounded-xl font-bold hover:bg-gray-200 transition border border-gray-200">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div> {{-- FIN ZONA WEB --}}

    {{-- ========================================== --}}
    {{-- RECIBO TÉRMICO DE ABONO (IMPRESIÓN)        --}}
    {{-- ========================================== --}}
    @if($datosRecibo)
    <div class="zona-impresion bg-white">
        <div class="flex items-center justify-between mb-3 border-b-2 border-dashed border-gray-400 pb-2">
            <div class="w-1/4">
                <img src="{{ asset('img/LOGO_POTOSI_01.png') }}" class="max-h-16 object-contain grayscale" style="filter: grayscale(100%);">
            </div>
            <div class="w-3/4 text-right">
                <h1 class="font-black text-2xl uppercase tracking-widest leading-none mb-1">IGLA POTOSÍ</h1>
                <p class="text-xs text-gray-600 font-bold mt-1">Instituto Técnico Gastronómico</p>
            </div>
        </div>

        <div class="flex justify-between items-end mb-4 border-b border-gray-800 pb-1">
            <h2 class="font-bold text-base uppercase tracking-wide">Comprobante de Pago</h2>
            <p class="text-sm">Nro: <span class="font-bold text-lg">{{ $datosRecibo['nro_recibo'] }}</span></p>
        </div>

        <div class="mb-4 text-sm bg-gray-50 p-2 rounded-lg border border-gray-100">
            <p class="mb-1"><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Estudiante:</span> <strong>{{ $datosRecibo['estudiante'] }}</strong></p>
            <p class="mb-1"><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Fecha:</span> {{ $datosRecibo['fecha'] }}</p>
            <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Cajero(a):</span> {{ $datosRecibo['cajero'] }}</p>
        </div>

        <div class="mb-5">
            <p class="text-[10px] font-bold text-gray-400 uppercase border-b border-gray-200 pb-1 mb-2">Por Concepto de:</p>
            <p class="font-black text-sm uppercase leading-tight">{{ $datosRecibo['concepto'] }}</p>
            <p class="text-xs text-gray-600 mt-1 font-bold">Abonado vía: {{ $datosRecibo['metodo_pago'] }}</p>
        </div>

        <div class="border-t-2 border-gray-800 pt-2 mb-6">
            <div class="flex justify-between text-sm mb-1 text-gray-600 font-bold">
                <span>Costo Total Original:</span>
                <span>{{ number_format($datosRecibo['costo_total'], 2) }} Bs</span>
            </div>
            <div class="flex justify-between text-base font-black mt-2">
                <span>MONTO ABONADO:</span>
                <span>{{ number_format($datosRecibo['monto_abonado_hoy'], 2) }} Bs</span>
            </div>

            @if($datosRecibo['cambio'] > 0)
                <div class="flex justify-between text-xs mt-1 text-gray-500 font-bold">
                    <span>Cambio Devuelto:</span>
                    <span>{{ number_format($datosRecibo['cambio'], 2) }} Bs</span>
                </div>
            @endif
            
            @if($datosRecibo['saldo_pendiente'] > 0)
                <div class="flex justify-between text-sm mt-3 pt-2 border-t border-dashed border-gray-300 font-bold text-red-600">
                    <span>NUEVO SALDO DEUDOR:</span>
                    <span>{{ number_format($datosRecibo['saldo_pendiente'], 2) }} Bs</span>
                </div>
            @else
                <div class="flex justify-between text-sm mt-3 pt-2 border-t border-dashed border-gray-300 font-black text-green-600">
                    <span>ESTADO DE DEUDA:</span>
                    <span>PAGADO COMPLETO</span>
                </div>
            @endif
        </div>

        <div class="text-center text-[10px] text-gray-500 border-t border-gray-300 pt-3">
            <p>Conserve este recibo como comprobante de pago.</p>
        </div>
    </div>
    @endif

    <style>
        .zona-impresion { display: none; }
        @media print {
            nav, aside, .ocultar-al-imprimir, .no-imprimir { display: none !important; }
            @page { margin: 0 !important; size: auto; }
            body, html { margin: 0 !important; padding: 0 !important; background-color: white !important; }
            main, main > div, .container, .px-4 { margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; border-radius: 0 !important; background: white !important; max-width: 100% !important; }
            .zona-impresion { display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; width: 100% !important; max-width: 100% !important; padding: 1cm 1.5cm !important; border: none !important; box-shadow: none !important; background: transparent !important; color: black !important; }
            .zona-impresion * { color: black !important; font-family: Arial, Helvetica, sans-serif !important; background: transparent !important; }
            .zona-impresion p, .zona-impresion div, .zona-impresion span { font-size: 11pt !important; line-height: 1.3 !important; }
        }
    </style>
</div>