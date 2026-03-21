<div class="fixed inset-0 bg-gray-900 bg-opacity-70 flex items-center justify-center z-50 backdrop-blur-sm animate-fade-in-down ocultar-al-imprimir">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl p-6 grid grid-cols-1 md:grid-cols-3 gap-6 relative max-h-[90vh]">

        <button wire:click="cerrarModal" class="absolute top-4 right-4 bg-gray-100 hover:bg-red-50 text-gray-400 hover:text-red-500 rounded-full w-8 h-8 flex items-center justify-center transition font-bold z-10">✕</button>

        {{-- LISTA DE PAGOS (IZQUIERDA) --}}
        <div class="col-span-1 border-r border-gray-100 pr-6 overflow-y-auto custom-scrollbar h-full max-h-[80vh]">
            <h3 class="text-xl font-black mb-6 text-gray-800 tracking-tight border-b border-gray-100 pb-4">
                <i class="fa-solid fa-list-check text-blue-500 mr-2"></i> Plan de Cuotas
            </h3>

            @php
                $pagosPorAnio = $pagos->groupBy(function($p) { return \Carbon\Carbon::parse($p->fecha_vencimiento)->year; });
            @endphp

            @forelse($pagosPorAnio as $anio => $listaPagos)
                @php
                    $listaPagosOrdenada = $listaPagos->sort(function($a, $b) {
                        if (str_contains($a->descripcion, 'PUA')) return -1;
                        if (str_contains($b->descripcion, 'PUA')) return 1;
                        return $a->fecha_vencimiento <=> $b->fecha_vencimiento;
                    });
                @endphp

                <div class="mb-6">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3 bg-gray-50 px-3 py-1 rounded">Gestión {{ $anio }}</h4>
                    
                    @foreach($listaPagosOrdenada as $p)
                        @php
                            $estadoColor = match($p->estado) {
                                'pagado' => 'bg-green-50 border-green-200 hover:bg-green-100',
                                'parcial' => 'bg-yellow-50 border-yellow-300 hover:bg-yellow-100',
                                'vencido' => 'bg-red-50 border-red-200 hover:bg-red-100',
                                default => 'bg-white border-gray-200 hover:bg-blue-50'
                            };
                            $isSelected = $pagoSeleccionado && $pagoSeleccionado->id_pago === $p->id_pago;
                            $borderClass = $isSelected ? 'ring-2 ring-blue-500 border-blue-500 shadow-md transform scale-[1.02]' : 'border shadow-sm';
                        @endphp

                        <div wire:click="seleccionarPago({{ $p->id_pago }})"
                             class="p-3.5 rounded-xl mb-3 cursor-pointer transition-all duration-200 {{ $estadoColor }} {{ $borderClass }}">
                            
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-gray-800 text-sm leading-tight">{{ $p->descripcion }}</p>
                                    <p class="text-[10px] text-gray-500 mt-1 font-mono"><i class="fa-regular fa-clock"></i> Vence: {{ \Carbon\Carbon::parse($p->fecha_vencimiento)->format('d/m/Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider
                                        {{ $p->estado == 'pagado' ? 'bg-green-200 text-green-800' : ($p->estado == 'parcial' ? 'bg-yellow-200 text-yellow-800' : 'bg-gray-200 text-gray-600') }}">
                                        {{ $p->estado }}
                                    </span>
                                </div>
                            </div>

                            @if($p->estado === 'parcial' || ($p->estado === 'pagado' && $p->monto_abonado > 0))
                                <div class="mt-3 w-full bg-white/50 rounded-full h-1.5 border border-black/5 overflow-hidden">
                                    <div class="bg-green-500 h-full transition-all duration-500" style="width: {{ ($p->monto_abonado / $p->monto_total) * 100 }}%"></div>
                                </div>
                                <p class="text-[10px] font-bold text-right mt-1 text-gray-600">
                                    {{ $p->monto_abonado }} / {{ $p->monto_total }} Bs
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="text-center py-10">
                    <i class="fa-regular fa-folder-open text-3xl text-gray-300 mb-2"></i>
                    <p class="text-gray-400 text-sm">No hay cuotas generadas.</p>
                </div>
            @endforelse
        </div>

        {{-- DETALLES DEL PAGO (DERECHA - CAJA DE COBRO) --}}
        <div class="col-span-2 bg-gray-50 rounded-xl overflow-y-auto custom-scrollbar h-[80vh]">
            <div class="p-6 pb-12">
                @if($pagoSeleccionado)

                    <div class="w-full bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gray-800 text-white p-5 flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-bold">{{ $pagoSeleccionado->descripcion }}</h3>
                                <p class="text-gray-400 text-xs mt-1">Vencimiento: {{ $pagoSeleccionado->fecha_vencimiento }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-400 block uppercase font-bold tracking-wider">Costo Cuota</span>
                                <span class="text-2xl font-black text-blue-400">{{ number_format($pagoSeleccionado->monto_total, 2) }} Bs</span>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="mb-8">
                                <div class="flex justify-between text-sm mb-2 font-bold">
                                    <span class="text-gray-600">Progreso de la cuota</span>
                                    <span class="{{ $pagoSeleccionado->estado == 'pagado' ? 'text-green-600' : 'text-blue-600' }}">
                                        Abonado: {{ number_format($pagoSeleccionado->monto_abonado, 2) }} Bs 
                                        / Resta: {{ number_format($pagoSeleccionado->monto_total - $pagoSeleccionado->monto_abonado, 2) }} Bs
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3 border border-gray-200 shadow-inner">
                                    <div class="bg-blue-500 h-full rounded-full transition-all duration-1000" style="width: {{ ($pagoSeleccionado->monto_abonado / $pagoSeleccionado->monto_total) * 100 }}%"></div>
                                </div>
                            </div>

                            @if($pagoSeleccionado->estado !== 'pagado')
                                <div class="bg-blue-50/50 p-5 rounded-xl border border-blue-100 mb-6 relative overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                                    <h4 class="font-black text-gray-800 mb-4 text-sm uppercase tracking-wide flex items-center gap-2">
                                        <i class="fa-solid fa-cash-register text-blue-500"></i> Procesar Pago
                                    </h4>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($metodosPago as $metodo)
                                            <div class="flex shadow-sm rounded-lg overflow-hidden border border-gray-300 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 transition-all bg-white">
                                                <span class="inline-flex items-center justify-center px-3 bg-gray-50 text-gray-600 text-xs font-bold w-24 border-r border-gray-200">
                                                    {{ $metodo->nombre }}
                                                </span>
                                                <input type="number" step="0.50" wire:model.live="montos.{{ $metodo->id_metodo_pago }}" 
                                                    class="flex-1 w-full px-3 py-2 border-none text-sm font-bold text-gray-800 focus:ring-0" placeholder="0.00">
                                                <button wire:click="llenarSaldo({{ $metodo->id_metodo_pago }})" class="px-3 bg-gray-50 text-blue-500 hover:bg-blue-100 hover:text-blue-700 transition font-bold text-[10px] uppercase border-l border-gray-200" title="Pagar deuda con este método">
                                                    Max
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-5 flex justify-between items-center bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                        <div class="mt-5 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                                            {{-- CAMBIO FECHA --}}
                                            <div class="mb-4 bg-orange-50 p-3 rounded-lg border border-orange-200">
                                                <label class="block text-[10px] font-black text-orange-800 uppercase mb-1 flex items-center gap-1">
                                                    <i class="fa-solid fa-calendar-day"></i> Fecha del Pago (Modo Migración)
                                                </label>
                                                <input type="date" wire:model="fechaPagoManual" 
                                                    class="w-full px-3 py-1.5 border border-orange-300 rounded text-sm font-bold text-gray-700 focus:ring-orange-500 focus:border-orange-500">
                                                <p class="text-[9px] text-orange-600 mt-1 leading-tight">Cambia esto solo si estás registrando un recibo antiguo. Por defecto es hoy.</p>
                                            </div>
                                            {{-- CÁLCULO DE CAMBIO --}}
                                            <div class="grid grid-cols-2 gap-4 mb-4 pb-4 border-b border-gray-100">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Efectivo Recibido (Billete)</label>
                                                    <div class="relative">
                                                        <input type="number" step="0.50" wire:model.live="efectivoRecibido" 
                                                            class="w-full pl-8 pr-3 py-2 border-gray-300 rounded-lg text-sm font-bold text-gray-800 focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="Ej: 100">
                                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <i class="fa-solid fa-money-bill-wave text-gray-400"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="bg-gray-50 rounded-lg p-2 text-right border border-gray-100 flex flex-col justify-center">
                                                    <span class="text-[10px] font-bold text-gray-500 uppercase block">Cambio a devolver</span>
                                                    <span class="text-xl font-black {{ $cambio > 0 ? 'text-red-500' : 'text-gray-400' }}">
                                                        {{ number_format($cambio, 2) }} <span class="text-xs">Bs</span>
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- TOTAL A INGRESAR Y BOTÓN --}}
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide block">Monto a Cobrar</span>
                                                    <span class="text-2xl font-black {{ $totalIngresado > ($pagoSeleccionado->monto_total - $pagoSeleccionado->monto_abonado) + 0.1 ? 'text-red-500' : 'text-green-600' }}">
                                                        {{ number_format($totalIngresado, 2) }} <span class="text-sm text-gray-400">Bs</span>
                                                    </span>
                                                </div>
                                                <button wire:click="procesarCobro" wire:loading.attr="disabled"
                                                    class="bg-blue-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-blue-700 shadow-md transition-all flex items-center gap-2 disabled:opacity-50"
                                                    {{ $totalIngresado <= 0 ? 'disabled' : '' }}>
                                                    <span wire:loading.remove wire:target="procesarCobro"><i class="fa-solid fa-check"></i> Efectuar Cobro</span>
                                                    <span wire:loading wire:target="procesarCobro"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @error('general') <span class="text-red-500 text-xs font-bold mt-2 block"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> @enderror
                                </div>
                            @else
                                <div class="bg-green-50 text-green-700 p-4 rounded-xl text-center font-bold mb-6 border border-green-200 flex flex-col items-center justify-center gap-1">
                                    <i class="fa-solid fa-circle-check text-3xl text-green-500 mb-1"></i>
                                    Cuota Completamente Pagada
                                </div>
                            @endif

                            {{-- HISTORIAL --}}
                            <div class="mt-8">
                                <h4 class="font-black text-gray-400 mb-3 text-[10px] uppercase tracking-widest border-b border-gray-100 pb-1">Historial de Recibos</h4>
                                
                                @if($pagoSeleccionado->transacciones->count() > 0)
                                    <div class="border border-gray-200 rounded-lg bg-white overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                                            <thead class="bg-gray-50 sticky top-0">
                                                <tr>
                                                    <th class="px-4 py-2 text-left font-bold text-gray-500">Fecha</th>
                                                    <th class="px-4 py-2 text-left font-bold text-gray-500">Ingreso Por</th>
                                                    <th class="px-4 py-2 text-right font-bold text-gray-500">Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-50">
                                                @foreach($pagoSeleccionado->transacciones as $t)
                                                    <tr class="hover:bg-gray-50 transition">
                                                        <td class="px-4 py-2 text-gray-500 font-mono text-xs">{{ \Carbon\Carbon::parse($t->fecha_transaccion)->format('d/m/Y H:i') }}</td>
                                                        <td class="px-4 py-2 font-bold text-gray-700">{{ $t->metodo->nombre }}</td>
                                                        <td class="px-4 py-2 text-right font-black text-blue-600">{{ number_format($t->monto, 2) }} Bs</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                                        <p class="text-gray-400 text-xs">Sin movimientos.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center opacity-40 py-32 flex flex-col items-center">
                        <i class="fa-solid fa-hand-holding-dollar text-7xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-500 mb-1">Caja Registradora</h3>
                        <p class="text-sm text-gray-400">Selecciona una cuota de la izquierda para cobrar.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    {{-- ========================================== --}}
    {{-- MODAL DE ÉXITO (SOBREPUESTO)               --}}
    {{-- ========================================== --}}
    @if($showModalExito)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-[60] no-imprimir animate-fade-in-down">
        <div class="bg-white p-8 rounded-2xl shadow-2xl text-center max-w-sm w-full border-t-4 border-green-500">
            <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <i class="fas fa-check text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-800 mb-2">¡Cobro Exitoso!</h3>
            <p class="text-gray-500 mb-8 text-sm">El pago de la cuota se registró correctamente en caja.</p>
            
            <div class="flex flex-col gap-3">
                <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-3 rounded-xl font-bold hover:bg-black transition w-full flex items-center justify-center gap-2 shadow-lg">
                    <i class="fa-solid fa-print"></i> Imprimir Recibo
                </button>

                <button wire:click="descargarReciboPdf" wire:loading.attr="disabled" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition w-full flex items-center justify-center gap-2 shadow-lg disabled:opacity-50">
                    <span wire:loading.remove wire:target="descargarReciboPdf"><i class="fa-solid fa-file-pdf"></i> Descargar PDF</span>
                    <span wire:loading wire:target="descargarReciboPdf"><i class="fa-solid fa-spinner fa-spin"></i> Generando PDF...</span>
                </button>

                <button wire:click="cerrarModalExito" class="bg-green-50 text-green-700 px-6 py-3 rounded-xl font-bold hover:bg-green-100 transition w-full">
                    Continuar Cobrando
                </button>
            </div>
        </div>
    </div>
    @endif
</div>