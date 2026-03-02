<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-70 backdrop-blur-sm z-50 animate-fade-in-down">
    <div class="max-w-sm w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden border-2 border-orange-200">
        
        <div class="bg-orange-50 px-5 py-3 border-b border-orange-100 flex justify-between items-center">
            <h3 class="text-md font-bold text-orange-800">
                <i class="fa-solid fa-bolt text-orange-500 mr-2"></i>Creación Rápida
            </h3>
            <button wire:click="closeCategoriaModal" class="text-orange-400 hover:text-orange-700 transition">&times;</button>
        </div>

        <form wire:submit.prevent="guardarCategoria" class="p-5 space-y-4" autocomplete="off">
            
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre de la Categoría</label>
                <input wire:model="nueva_categoria_nombre" type="text" autocomplete="off" placeholder="Ej: Panadería"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                @error('nueva_categoria_nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
                <button type="button" wire:click="closeCategoriaModal" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="guardarCategoria"
                    class="px-4 py-2 text-sm font-bold text-white bg-gray-800 hover:bg-black rounded-lg transition-colors flex items-center gap-2 disabled:opacity-50">
                    <span wire:loading.remove wire:target="guardarCategoria">Crear</span>
                    <span wire:loading wire:target="guardarCategoria"><i class="fa-solid fa-spinner fa-spin"></i></span>
                </button>
            </div>
            
        </form>
    </div>
</div>