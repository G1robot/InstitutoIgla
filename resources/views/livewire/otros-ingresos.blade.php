<div class="px-4 pb-8">

    {{-- SISTEMA WEB --}}
    <div class="ocultar-al-imprimir">
        
        <div class="mb-8 border-l-4 border-green-500 pl-4">
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">OTROS INGRESOS DE CAJA</h2>
            <p class="text-sm text-gray-500 mt-1">Registra donaciones, multas, patrocinios y entradas extras de dinero.</p>
        </div>

        @error('general') 
            <div class="mb-4 bg-red-50 text-red-600 p-3 rounded-lg border border-red-200 font-bold"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>
        @enderror

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- COLUMNA IZQUIERDA: FORMULARIO --}}
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 border-t-4 border-t-green-500 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3">
                        <i class="fa-solid fa-hand-holding-dollar text-green-500"></i> Nuevo Ingreso
                    </h3>

                    <form wire:submit.prevent="guardarIngreso" class="space-y-4" autocomplete="off" wire:key="form-ingreso-{{ $formKey }}">
                        
                        {{-- Origen --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre / Origen (Opcional)</label>
                            <input type="text" wire:model="nombre_origen" autocomplete="off" placeholder="Ej: Alcaldía, Sr. Perez..."
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-shadow">
                            @error('nombre_origen') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Concepto --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Concepto Principal *</label>
                            <input type="text" wire:model="concepto" autocomplete="off" placeholder="Ej: Donación, Multa por atraso..."
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-shadow">
                            @error('concepto') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Descripción --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Detalles Extras</label>
                            <textarea wire:model="descripcion" rows="2"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-shadow text-sm resize-none bg-gray-50 focus:bg-white" 
                                placeholder="Notas adicionales..."></textarea>
                            @error('descripcion') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Monto y Método --}}
                        <div class="grid grid-cols-2 gap-4 bg-green-50 p-3 rounded-lg border border-green-100">
                            <div>
                                <label class="block text-xs font-bold text-green-800 uppercase mb-1">Monto (Bs) *</label>
                                <div class="relative">
                                    <input type="number" step="0.10" wire:model="monto" autocomplete="off"
                                        class="w-full border-none rounded-md py-2 pl-2 pr-8 focus:ring-2 focus:ring-green-500 font-black text-lg text-green-600 text-right shadow-sm">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-green-500 font-bold text-sm">Bs</span>
                                    </div>
                                </div>
                                @error('monto') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-green-800 uppercase mb-1">Ingresa a *</label>
                                <select wire:model="id_metodo_pago" class="w-full border-none rounded-md py-2 px-2 focus:ring-2 focus:ring-green-500 text-sm font-bold text-gray-700 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    @foreach($metodosPago as $metodo)
                                        <option value="{{ $metodo->id_metodo_pago }}">
                                            {{ $metodo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_metodo_pago') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Fecha --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Fecha y Hora</label>
                            <input type="datetime-local" wire:model="fecha_ingreso" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm text-gray-600">
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <button type="submit" wire:loading.attr="disabled" wire:target="guardarIngreso"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                                <span wire:loading.remove wire:target="guardarIngreso"><i class="fa-solid fa-arrow-right-to-bracket"></i> Registrar Ingreso</span>
                                <span wire:loading wire:target="guardarIngreso"><i class="fa-solid fa-spinner fa-spin"></i> Procesando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- COLUMNA DERECHA: LISTADO --}}
            <div class="lg:col-span-2">
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 bg-white p-3 rounded-xl shadow-sm border border-gray-100 gap-4">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-sm font-bold text-gray-500"><i class="fa-regular fa-calendar text-green-500"></i> Mes:</span>
                        <input type="month" wire:model.live="mesFilter" class="border border-gray-200 rounded-lg p-2 text-sm text-gray-700 font-bold focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar ingreso..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 text-sm bg-gray-50 focus:bg-white">
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Origen y Detalle</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Monto</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($ingresos as $ing)
                                    <tr class="hover:bg-green-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($ing->fecha_ingreso)->format('d M, Y') }}</div>
                                            <div class="text-xs">{{ \Carbon\Carbon::parse($ing->fecha_ingreso)->format('H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900 leading-tight">{{ $ing->concepto }}</div>
                                            <div class="text-[10px] bg-gray-100 px-2 py-0.5 rounded inline-block mt-1 text-gray-600 font-bold border border-gray-200">
                                                <i class="fa-regular fa-user mr-1"></i> {{ $ing->nombre_origen ?: 'Sin Origen' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="text-base font-black text-green-600">+{{ number_format($ing->monto_total, 2) }}</span>
                                            <span class="text-xs font-bold text-green-400">Bs</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button wire:click="eliminar({{ $ing->id_ingreso }})" 
                                                onclick="confirm('¿Eliminar este ingreso? Se descontará el saldo de caja.') || event.stopImmediatePropagation()" 
                                                class="text-gray-400 hover:text-red-600 transition p-2 rounded-lg hover:bg-red-100">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-12 text-gray-400">
                                            <i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300"></i>
                                            <p>No hay ingresos extras registrados con esta búsqueda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($ingresos, 'hasPages') && $ingresos->hasPages())
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">{{ $ingresos->links() }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- MODAL DE ÉXITO --}}
        @if($showModalExito)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 no-imprimir animate-fade-in-down">
            <div class="bg-white p-8 rounded-2xl shadow-2xl text-center max-w-sm w-full border-t-4 border-green-500">
                <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                    <i class="fas fa-check text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-800 mb-2">¡Ingreso Registrado!</h3>
                
                <div class="flex flex-col gap-3 mt-6">
                    <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-3 rounded-xl font-bold hover:bg-black transition w-full flex items-center justify-center gap-2 shadow-lg">
                        <i class="fa-solid fa-print"></i> Imprimir Comprobante
                    </button>

                    <button wire:click="descargarReciboPdf" wire:loading.attr="disabled" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition w-full flex items-center justify-center gap-2 shadow-lg disabled:opacity-50">
                        <span wire:loading.remove wire:target="descargarReciboPdf"><i class="fa-solid fa-file-pdf"></i> Descargar PDF</span>
                        <span wire:loading wire:target="descargarReciboPdf"><i class="fa-solid fa-spinner fa-spin"></i> Generando PDF...</span>
                    </button>
                    
                    <button wire:click="cerrarModalExito" class="bg-green-50 text-green-600 px-6 py-3 rounded-xl font-bold hover:bg-green-100 transition w-full">
                        Nuevo Ingreso
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div> {{-- FIN SISTEMA WEB --}}

    {{-- RECIBO PARA IMPRIMIR --}}
    @if($datosRecibo)
    <div class="zona-impresion bg-white">
        <div class="flex items-center justify-between mb-3 border-b-2 border-dashed border-gray-400 pb-2">
            <div class="w-1/4">
                <img src="{{ asset('img/LOGO_POTOSI_01.png') }}" class="max-h-16 object-contain grayscale" style="filter: grayscale(100%);">
            </div>
            <div class="w-3/4 text-right">
                <h1 class="font-black text-2xl uppercase tracking-widest leading-none mb-1">IGLA POTOSÍ</h1>
                <p class="text-xs text-gray-600 font-bold mt-1">Instituto Técnico Gastronómico</p>
                <p class="text-[10px] text-gray-500 mt-0.5">Telfs 74289575 | Calle Tarija #30, Potosí</p>
            </div>
        </div>

        <div class="flex justify-between items-end mb-4 border-b border-gray-800 pb-1">
            <h2 class="font-bold text-lg uppercase tracking-wide">Comprobante de Ingreso</h2>
            <p class="text-sm">Nro: <span class="font-bold text-lg">{{ $datosRecibo['nro_recibo'] }}</span></p>
        </div>

        <div class="flex justify-between mb-4 text-sm bg-gray-50 p-2 rounded-lg border border-gray-100">
            <div class="text-left w-1/2 pr-2">
                <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Recibimos de:</span> {{ $datosRecibo['origen'] }}</p>
                <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Ingresado a:</span> {{ $datosRecibo['metodo_pago'] }}</p>
            </div>
            <div class="text-right w-1/2 pl-2">
                <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Fecha:</span> {{ $datosRecibo['fecha'] }}</p>
                <p><span class="text-gray-500 uppercase text-[10px] font-bold mr-1">Cajero:</span> {{ $datosRecibo['cajero'] }}</p>
            </div>
        </div>

        <div class="mb-5 text-sm px-2">
            <h3 class="font-bold text-[10px] text-gray-500 uppercase mb-1 border-b border-gray-200 pb-1">Por Concepto De</h3>
            <p class="text-base font-bold mt-1">{{ $datosRecibo['concepto'] }}</p>
            @if($datosRecibo['descripcion']) <p class="text-xs text-gray-600 mt-1 italic">{{ $datosRecibo['descripcion'] }}</p> @endif
        </div>

        <div class="flex justify-end mb-10">
            <div class="w-3/4 sm:w-1/2 text-sm">
                <div class="flex justify-between font-black text-lg border-t-2 border-b-2 py-1.5 border-gray-800 bg-gray-50 px-2 rounded-sm">
                    <span>IMPORTE TOTAL:</span>
                    <span>{{ number_format($datosRecibo['monto'], 2) }} Bs</span>
                </div>
            </div>
        </div>
        
        <div class="text-center text-[11px] text-gray-500 border-t border-gray-300 pt-3 mt-10">
            <p>Comprobante de ingreso válido para control interno.</p>
        </div>
    </div>
    @endif

    <style>
        .zona-impresion { display: none; }
        @media print {
            nav, aside, .ocultar-al-imprimir, .no-imprimir { display: none !important; }
            @page { margin: 0 !important; size: auto; }
            body, html { margin: 0 !important; padding: 0 !important; background-color: white !important; }
            main, main > div, .container, .px-4 { margin: 0 !important; padding: 0 !important; border: none !important; background: white !important; max-width: 100% !important; }
            .zona-impresion .flex { display: flex !important; }
            .zona-impresion { display: block !important; position: absolute !important; top: 0 !important; left: 0 !important; width: 100% !important; padding: 1cm 1.5cm !important; background: white !important; z-index: 9999 !important; color: black !important;}
            .zona-impresion * { color: black !important; font-family: Arial, Helvetica, sans-serif !important; background: transparent !important; }
            .zona-impresion p, .zona-impresion div { font-size: 11pt !important; line-height: 1.3 !important; }
            .zona-impresion h1 { font-size: 16pt !important; margin-bottom: 2px !important; }
            .zona-impresion h2 { font-size: 13pt !important; margin-bottom: 0 !important; text-transform: uppercase !important; }
        }
    </style>
</div>