<div class="px-4 pb-8">

    {{-- ========================================== --}}
    {{-- SISTEMA WEB (SE OCULTA AL IMPRIMIR)        --}}
    {{-- ========================================== --}}
    <div class="ocultar-al-imprimir">
        
        <div class="mb-8 border-l-4 border-red-500 pl-4">
            <h2 class="text-2xl font-black text-gray-800 tracking-tight">REGISTRO DE GASTOS Y COMPRAS</h2>
            <p class="text-sm text-gray-500 mt-1">Registra las salidas de dinero (egresos) de la caja actual.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- COLUMNA IZQUIERDA: FORMULARIO DE REGISTRO --}}
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 border-t-4 border-t-red-500 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-5 flex items-center gap-2 border-b border-gray-100 pb-3">
                        <i class="fas fa-file-invoice-dollar text-red-500"></i> Nuevo Egreso
                    </h3>

                    <form wire:submit.prevent="guardarEgreso" class="space-y-4" autocomplete="off" wire:key="form-egreso-{{ $formKey }}">
                        
                        {{-- Concepto --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Concepto / Razón Principal *</label>
                            <textarea wire:model="concepto" rows="2" autocomplete="off"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-shadow" 
                                placeholder="Ej: Mantenimiento de computadoras..."></textarea>
                            @error('concepto') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Descripción Opcional --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Observaciones Extras</label>
                            <textarea wire:model="descripcion" rows="2" autocomplete="off"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-shadow text-sm resize-none bg-gray-50 focus:bg-white" 
                                placeholder="Detalles técnicos, materiales, etc..."></textarea>
                            @error('descripcion') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Monto y Método --}}
                        <div class="grid grid-cols-2 gap-4 bg-red-50 p-3 rounded-lg border border-red-100">
                            <div>
                                <label class="block text-xs font-bold text-red-800 uppercase mb-1">Monto (Bs) *</label>
                                <div class="relative">
                                    <input type="number" step="0.10" wire:model="monto" autocomplete="off"
                                        class="w-full border-none rounded-md py-2 pl-2 pr-8 focus:ring-2 focus:ring-red-500 font-black text-lg text-red-600 text-right shadow-sm">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-red-400 font-bold text-sm">Bs</span>
                                    </div>
                                </div>
                                @error('monto') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-red-800 uppercase mb-1">Se paga con *</label>
                                <select wire:model="id_metodo_pago" class="w-full border-none rounded-md py-2 px-2 focus:ring-2 focus:ring-red-500 text-sm font-bold text-gray-700 shadow-sm">
                                    <option value="">Seleccione...</option>
                                    @foreach($metodosPago as $metodo)
                                        <option value="{{ $metodo->id_metodo_pago }}">
                                            {{ $metodo->nombre }} {{ $metodo->es_efectivo ? '(Caja)' : '(Banco)' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_metodo_pago') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Proveedor --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Proveedor</label>
                            <div class="flex gap-2">
                                <select wire:model="id_proveedor" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-shadow text-sm bg-white">
                                    <option value="">Sin proveedor / Varios</option>
                                    @foreach($proveedores as $prov)
                                        <option value="{{ $prov->id_proveedor }}">{{ $prov->nombre_empresa }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="openModalProveedor" class="bg-gray-100 border border-gray-300 px-4 rounded-lg text-gray-600 hover:bg-red-50 hover:text-red-600 hover:border-red-300 font-bold transition-colors" title="Crear Nuevo Proveedor">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Detalles Factura --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Comprobante</label>
                                <select wire:model="tipo_comprobante" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-shadow text-sm bg-white">
                                    <option value="recibo">Recibo Simple</option>
                                    <option value="factura">Factura</option>
                                    <option value="vale">Vale / Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nro. Doc.</label>
                                <input type="text" wire:model="nro_factura" autocomplete="off" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-shadow text-sm" placeholder="Opcional">
                            </div>
                        </div>

                        {{-- Fecha y Hora --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Fecha y Hora</label>
                            <input type="datetime-local" wire:model="fecha_egreso" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-shadow text-sm text-gray-600">
                        </div>

                        

                        <div class="pt-4 border-t border-gray-100">
                            <button type="submit" 
                                wire:loading.attr="disabled" 
                                wire:target="guardarEgreso"
                                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="guardarEgreso"><i class="fa-solid fa-arrow-right-from-bracket"></i> Registrar Salida de Dinero</span>
                                <span wire:loading wire:target="guardarEgreso"><i class="fa-solid fa-spinner fa-spin"></i> Procesando...</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            {{-- COLUMNA DERECHA: LISTADO DE EGRESOS --}}
            <div class="lg:col-span-2">
                {{-- Filtros --}}
                <div class="flex flex-col sm:flex-row justify-between items-center mb-4 bg-white p-3 rounded-xl shadow-sm border border-gray-100 gap-4">
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-sm font-bold text-gray-500"><i class="fa-regular fa-calendar text-red-400"></i> Mes:</span>
                        <input type="month" wire:model.live="mesFilter" class="border border-gray-200 rounded-lg p-2 text-sm text-gray-700 font-bold focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div class="relative w-full sm:w-72">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar gasto..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm bg-gray-50 focus:bg-white transition-shadow">
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha / Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Detalle del Egreso</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pago con</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($egresos as $egreso)
                                    <tr class="hover:bg-red-50 transition-colors group">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($egreso->fecha_egreso)->format('d M, Y') }}</div>
                                            <div class="text-xs">{{ \Carbon\Carbon::parse($egreso->fecha_egreso)->format('H:i') }}</div>
                                        </td>
                                        
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900 mb-1 leading-tight">{{ $egreso->concepto }}</div>
                                            @if($egreso->descripcion)
                                                <div class="text-xs text-gray-500 mb-2 italic">{{ Str::limit($egreso->descripcion, 80) }}</div>
                                            @endif
                                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                                <span class="text-[10px] font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-200">
                                                    <i class="fas fa-building mr-1"></i> {{ $egreso->proveedor->nombre_empresa ?? 'Sin Proveedor' }}
                                                </span>
                                                @if($egreso->nro_factura) 
                                                    <span class="text-[10px] font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded border border-blue-100">
                                                        {{ ucfirst($egreso->tipo_comprobante) }}: {{ $egreso->nro_factura }}
                                                    </span> 
                                                @else
                                                    <span class="text-[10px] font-bold bg-gray-50 text-gray-500 px-2 py-0.5 rounded border border-gray-200">
                                                        {{ ucfirst($egreso->tipo_comprobante) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full border {{ $egreso->metodoPago->es_efectivo ? 'bg-green-50 text-green-700 border-green-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                                <i class="fa-solid {{ $egreso->metodoPago->es_efectivo ? 'fa-cash-register' : 'fa-building-columns' }} mr-1.5 mt-0.5"></i>
                                                {{ $egreso->metodoPago->nombre }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <span class="text-base font-black text-red-600">-{{ number_format($egreso->monto, 2) }}</span>
                                            <span class="text-xs font-bold text-red-400">Bs</span>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button wire:click="eliminar({{ $egreso->id_egreso }})" 
                                                onclick="confirm('¿Estás seguro de eliminar este registro de egreso? Se restaurará el saldo en caja.') || event.stopImmediatePropagation()" 
                                                class="text-gray-400 hover:text-red-600 transition p-2 rounded-lg hover:bg-red-100" title="Eliminar registro">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-12 text-gray-400">
                                            <i class="fa-solid fa-file-invoice-dollar text-4xl mb-3 text-gray-300"></i>
                                            <p>No hay gastos registrados en este mes o con esa búsqueda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(method_exists($egresos, 'hasPages') && $egresos->hasPages())
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                            {{ $egresos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- MODAL NUEVO PROVEEDOR --}}
        @if($showModalProveedor)
            <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-70 backdrop-blur-sm z-50 animate-fade-in-down">
                <div class="max-w-sm w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden border-2 border-red-200">
                    <div class="bg-red-50 px-5 py-3 border-b border-red-100 flex justify-between items-center">
                        <h3 class="text-md font-bold text-red-800">
                            <i class="fa-solid fa-truck-fast text-red-500 mr-2"></i>Creación Rápida
                        </h3>
                        <button wire:click="$set('showModalProveedor', false)" class="text-red-400 hover:text-red-700 transition text-lg">&times;</button>
                    </div>
                    <form wire:submit.prevent="guardarProveedor" class="p-5 space-y-4" autocomplete="off">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Empresa / Beneficiario *</label>
                            <input type="text" wire:model="nuevo_proveedor_nombre" autocomplete="off" placeholder="Ej: Librería Paris"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-shadow">
                            @error('nuevo_proveedor_nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">NIT / CI (Opcional)</label>
                            <input type="text" wire:model="nuevo_proveedor_nit" autocomplete="off" placeholder="Ej: 12345678"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-shadow">
                        </div>
                        <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="$set('showModalProveedor', false)" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancelar</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="guardarProveedor" class="px-4 py-2 text-sm font-bold text-white bg-gray-800 hover:bg-black rounded-lg transition-colors flex items-center gap-2">
                                <span wire:loading.remove wire:target="guardarProveedor">Crear</span>
                                <span wire:loading wire:target="guardarProveedor"><i class="fa-solid fa-spinner fa-spin"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- MODAL DE ÉXITO (MODO PANTALLA) --}}
        @if($showModalExito)
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 no-imprimir animate-fade-in-down">
            <div class="bg-white p-8 rounded-2xl shadow-2xl text-center max-w-sm w-full border-t-4 border-red-500">
                <div class="w-20 h-20 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                    <i class="fas fa-check text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-800 mb-2">¡Egreso Registrado!</h3>
                <p class="text-gray-500 mb-8 text-sm">El comprobante de salida de dinero fue guardado exitosamente.</p>
                
                <div class="flex flex-col gap-3">
                    <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-3 rounded-xl font-bold hover:bg-black transition w-full flex items-center justify-center gap-2 shadow-lg">
                        <i class="fa-solid fa-print"></i> Imprimir Comprobante
                    </button>
                    <button wire:click="cerrarModalExito" class="bg-red-50 text-red-600 px-6 py-3 rounded-xl font-bold hover:bg-red-100 transition w-full">
                        Nuevo Egreso
                    </button>
                </div>
            </div>
        </div>
        @endif

    </div> {{-- FIN DEL DIV OCULTAR-AL-IMPRIMIR --}}

    {{-- ========================================== --}}
    {{-- RECIBO PARA IMPRIMIR (ESPECÍFICO DE EGRESOS)--}}
    {{-- ========================================== --}}
    @if($datosRecibo)
    <div class="zona-impresion bg-white">
        
        <div class="text-center mb-4 border-b-2 border-dashed border-gray-400 pb-4">
            <h1 class="font-black text-2xl uppercase tracking-widest">IGLA POTOSÍ</h1>
            <p class="text-sm">Instituto Técnico Gastronómico</p>
            <p class="text-sm">Telfs 74289575</p>
            <p class="text-xs">Calle Tarija #30, Potosí - Bolivia</p>
        </div>

        <div class="text-center mb-4">
            <h2 class="font-bold text-lg uppercase">Comprobante de Egreso</h2>
            <p class="text-sm">Nro: <strong>{{ $datosRecibo['nro_recibo'] }}</strong></p>
        </div>

        <div class="mb-4 text-sm border-b border-gray-300 pb-3">
            <p class="mb-1"><strong>Fecha y Hora:</strong> {{ $datosRecibo['fecha'] }}</p>
            <p class="mb-1"><strong>Cajero Responsable:</strong> {{ $datosRecibo['cajero'] }}</p>
            <p class="mb-1"><strong>Método de Pago:</strong> {{ $datosRecibo['metodo_pago'] }}</p>
            <p class="mb-1 mt-3"><strong>Beneficiario / Proveedor:</strong> {{ $datosRecibo['proveedor'] }}</p>
        </div>

        <div class="mb-6">
            <h3 class="font-bold text-sm uppercase mb-1 border-b border-gray-800">Concepto del Gasto</h3>
            <p class="text-sm font-bold mt-2">{{ $datosRecibo['concepto'] }}</p>
            @if($datosRecibo['descripcion'])
                <p class="text-xs text-gray-600 mt-1 italic">{{ $datosRecibo['descripcion'] }}</p>
            @endif
        </div>

        <div class="flex justify-end mb-10">
            <div class="w-2/3 text-sm">
                <div class="flex justify-between font-black text-lg border-t-2 border-b-2 py-2 border-gray-800 mt-2">
                    <span>IMPORTE TOTAL:</span>
                    <span>{{ number_format($datosRecibo['monto'], 2) }} Bs</span>
                </div>
            </div>
        </div>

        {{-- LÍNEAS DE FIRMA PARA RESPALDO --}}
        <div class="mt-12 flex justify-between px-4">
            <div class="text-center w-5/12">
                <div class="border-t border-black pt-1">
                    <p class="font-bold text-xs">Entregué Conforme</p>
                    <p class="text-[10px] text-gray-500">{{ $datosRecibo['cajero'] }}</p>
                </div>
            </div>
            <div class="text-center w-5/12">
                <div class="border-t border-black pt-1">
                    <p class="font-bold text-xs">Recibí Conforme</p>
                    <p class="text-[10px] text-gray-500">Firma Beneficiario / CI</p>
                </div>
            </div>
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

        .zona-impresion {
            display: block !important; position: absolute !important; top: 0 !important; left: 0 !important;
            width: 100% !important; padding: 1.5cm 2cm !important; border: none !important; box-shadow: none !important;
            background: transparent !important; border-radius: 0 !important; color: black !important;
        }

        .zona-impresion * { color: black !important; font-family: Arial, Helvetica, sans-serif !important; background: transparent !important; }
        
        .zona-impresion p, .zona-impresion td, .zona-impresion th, .zona-impresion span, .zona-impresion div { font-size: 11pt !important; line-height: 1.4 !important; }
        .zona-impresion h1 { font-size: 18pt !important; margin-bottom: 5px !important; }
        .zona-impresion h2 { font-size: 14pt !important; margin-bottom: 5px !important; text-transform: uppercase !important; }

        .zona-impresion table { width: 100% !important; table-layout: auto !important; border-collapse: collapse !important; border: none !important; }
        .zona-impresion th, .zona-impresion td { border: none !important; border-bottom: 1px dashed #ccc !important; padding: 6px 0 !important; }
        .zona-impresion thead th { border-bottom: 2px solid black !important; }
    }
</style>

</div>

