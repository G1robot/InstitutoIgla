<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 animate-fade-in-down">
    <div class="max-w-md w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden border-t-4 border-orange-500">
        
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fa-solid fa-folder mr-2 text-orange-500"></i>
                {{ $categoria_id ? 'Editar Categoría' : 'Nueva Categoría' }}
            </h2>
            <button wire:click="closeModal" class="text-gray-400 hover:text-red-500 transition text-xl">&times;</button>
        </div>

        <form wire:submit.prevent="guardarCategoria" class="p-6 space-y-4" autocomplete="off">
            
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre de la Categoría *</label>
                <input wire:model="nombre" type="text" autocomplete="off" placeholder="Ej: Panadería"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4">
                <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                
                <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="guardarCategoria"
                    class="px-5 py-2.5 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    
                    <span wire:loading.remove wire:target="guardarCategoria">
                        <i class="fa-solid fa-check"></i> {{ $categoria_id ? 'Guardar Cambios' : 'Crear Categoría' }}
                    </span>

                    <span wire:loading wire:target="guardarCategoria">
                        <i class="fa-solid fa-spinner fa-spin"></i> Procesando...
                    </span>
                </button>
            </div>
            
        </form>
    </div>
</div>