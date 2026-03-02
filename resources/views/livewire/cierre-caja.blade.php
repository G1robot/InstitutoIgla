<div>
    @if($showModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-70 backdrop-blur-sm z-[100]">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 animate-fade-in-down">
                
                <div class="text-center mb-6">
                    <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-lock text-3xl text-red-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Cerrar Turno</h2>
                    <p class="text-gray-500 text-sm mt-1">Para finalizar, ingresa el dinero físico que contaste en tu caja.</p>
                </div>

                <form wire:submit.prevent="confirmarCierre" class="space-y-5">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Monto Físico en Caja *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Bs</span>
                            </div>
                            <input type="number" step="0.10" wire:model="monto_fisico" 
                                class="w-full pl-10 border-2 border-gray-300 rounded-lg p-3 text-lg font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                                placeholder="0.00">
                        </div>
                        @error('monto_fisico') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-2 mt-4">
                        <button type="button" wire:click="$set('showModal', false)" class="w-1/3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 rounded-lg transition">
                            Cancelar
                        </button>
                        <button type="submit" class="w-2/3 bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg shadow-lg transition flex justify-center items-center gap-2">
                            <i class="fas fa-check-circle"></i> Confirmar
                        </button>
                    </div>
                    
                </form>

            </div>
        </div>
    @endif
</div>