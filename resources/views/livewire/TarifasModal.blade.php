<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 animate-fade-in-down">
    <div class="max-w-md w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden">
        
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fa-solid fa-tag mr-2 text-orange-500"></i>
                {{ $tarifa_id ? 'Editar Tarifa' : 'Nueva Tarifa' }}
            </h2>
            <button wire:click="closeModal" class="text-gray-400 hover:text-red-500 transition text-xl">&times;</button>
        </div>

        <form wire:submit.prevent="enviarClick" class="p-6 space-y-5" autocomplete="off">
            
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Código de Tarifa *</label>
                <div class="relative">
                    <input wire:model="codigo" type="text" autocomplete="off" placeholder="Ej: PUA, PUP"
                        class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow uppercase font-bold text-gray-800">
                </div>
                <p class="text-xs text-gray-400 mt-1">Identificador único (PUA = Plan Único Ahorro).</p>
                @error('codigo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Monto (Bs) *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-bold">Bs</span>
                    </div>
                    <input wire:model="monto" type="number" step="0.50" autocomplete="off"
                        class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 text-lg font-black text-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                </div>
                @error('monto') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Gestión (Opcional)</label>
                <input wire:model="gestion" type="number" autocomplete="off" placeholder="{{ date('Y') }}"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                {{-- <p class="text-[11px] text-gray-400 mt-1"><i class="fa-solid fa-circle-info mr-1"></i>Dejar vacío si el cobro es Permanente (Ej: PUP).</p> --}}
                @error('gestion') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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
                        <i class="fa-solid fa-check"></i> {{ $tarifa_id ? 'Guardar Cambios' : 'Registrar' }}
                    </span>

                    <span wire:loading wire:target="enviarClick" class="flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin"></i> Procesando...
                    </span>
                </button>
            </div>
            
        </form>
    </div>
</div>