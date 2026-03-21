<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 animate-fade-in-down">
    <div class="max-w-3xl w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden">
        
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fa-solid fa-layer-group mr-2 text-orange-500"></i>
                {{ $plan_id ?? false ? 'Editar Plan' : 'Nuevo Plan' }}
            </h2>
            <button wire:click="closeModal" class="text-gray-400 hover:text-red-500 transition text-xl">&times;</button>
        </div>

        <form wire:submit.prevent="enviarClick" class="p-6" autocomplete="off">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="space-y-5">
                    <h3 class="text-xs font-black text-gray-400 tracking-wider border-b pb-2">DATOS GENERALES</h3>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre del Plan *</label>
                        <input wire:model="nombre" type="text" autocomplete="off" placeholder="Ej: Plan Ahorro"
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow font-bold text-gray-800">
                        @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Duración (Años)</label>
                            <input wire:model="duracion_anios" type="number" min="0" autocomplete="off"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                            @error('duracion_anios') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Duración (Meses)</label>
                            <input wire:model="duracion_meses" type="number" autocomplete="off"
                                class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                            @error('duracion_meses') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400"><i class="fa-solid fa-circle-info mr-1"></i>Puedes combinar ambos. Ej: 1 Año y 6 Meses.</p>
                </div>

                <div class="space-y-5">
                    <h3 class="text-xs font-black text-gray-400 tracking-wider border-b pb-2">COSTOS Y MODALIDAD</h3>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Modalidad de Pago *</label>
                        <select wire:model="tipo_pago" 
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-white font-bold text-gray-700">
                            <option value="">Seleccione...</option>
                            <option value="anual">Cobro Anual (Ej: Plan Ahorro 3 cuotas)</option>
                            <option value="mensual">Cobro Mensual (Ej: Plan Regular)</option>
                            {{-- NUEVA OPCIÓN AÑADIDA AQUÍ --}}
                            <option value="unico">Pago Único (Ej: Plan al Contado)</option>
                        </select>
                        @error('tipo_pago') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Costo Anual</label>
                            <div class="relative">
                                <input wire:model="costo_anual" type="number" step="0.50" autocomplete="off"
                                    class="w-full pr-8 border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-400 text-xs font-bold">Bs</span>
                                </div>
                            </div>
                            @error('costo_anual') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Costo Mensual</label>
                            <div class="relative">
                                <input wire:model="costo_mensual" type="number" step="0.50" autocomplete="off"
                                    class="w-full pr-8 border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow text-orange-600 font-bold">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-orange-400 text-xs font-bold">Bs</span>
                                </div>
                            </div>
                            @error('costo_mensual') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- NUEVO CAMPO: COSTO TOTAL (AL CONTADO) --}}
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                        <label class="block text-xs font-black text-gray-700 uppercase mb-1">Costo Total (Plan al Contado)</label>
                        <div class="relative">
                            <input wire:model="costo_total" type="number" step="0.50" autocomplete="off" placeholder="Ej: 8000.00"
                                class="w-full pr-8 border-2 border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-shadow text-green-700 font-black text-lg bg-white shadow-inner">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-green-500 text-sm font-bold">Bs</span>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1.5 leading-tight"><i class="fa-solid fa-circle-info mr-1"></i>Llenar solo si tiene un precio especial cerrado. Si se deja en blanco, el sistema lo calculará automáticamente.</p>
                        @error('costo_total') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                
                {{-- Spinner anti-doble clic --}}
                <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="enviarClick"
                    class="px-5 py-2.5 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    
                    <span wire:loading.remove wire:target="enviarClick" class="flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> {{ $plan_id ?? false ? 'Guardar Cambios' : 'Registrar Plan' }}
                    </span>

                    <span wire:loading wire:target="enviarClick" class="flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin"></i> Procesando...
                    </span>
                </button>
            </div>
            
        </form>
    </div>
</div>