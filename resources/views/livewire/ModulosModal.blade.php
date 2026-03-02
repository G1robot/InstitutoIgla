<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 animate-fade-in-down">
    <div class="max-w-md w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden">
        
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fa-solid fa-cube mr-2 text-orange-500"></i>
                {{ $modulo_id ? 'Editar Módulo' : 'Nuevo Módulo' }}
            </h2>
            <button wire:click="closeModal" class="text-gray-400 hover:text-red-500 transition text-xl">&times;</button>
        </div>

        <form wire:submit.prevent="guardarModulo" class="p-6 space-y-5" autocomplete="off">
            
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre del Módulo *</label>
                <input wire:model="nombre" type="text" autocomplete="off" placeholder="Ej: Repostería Básica"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Categoría *</label>
                <div class="flex gap-2">
                    <select wire:model="id_categoria_modulo" 
                        class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-white">
                        <option value="">Seleccione una categoría...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id_categoria_modulo }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    
                    {{-- Botón para nueva categoría --}}
                    <button type="button" wire:click="openCategoriaModal" 
                        class="bg-gray-100 text-gray-600 border border-gray-300 px-4 rounded-lg hover:bg-orange-50 hover:text-orange-600 hover:border-orange-300 transition-colors" title="Crear Nueva Categoría rápida">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                @error('id_categoria_modulo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Costo del Módulo (Bs) *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-bold">Bs</span>
                    </div>
                    <input wire:model="costo" type="number" step="0.50" autocomplete="off"
                        class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 text-lg font-black text-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                </div>
                @error('costo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                
                <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="guardarModulo"
                    class="px-5 py-2.5 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="guardarModulo"><i class="fa-solid fa-check mr-1"></i> Guardar</span>
                    <span wire:loading wire:target="guardarModulo"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Guardando...</span>
                </button>
            </div>
            
        </form>
    </div>
</div>