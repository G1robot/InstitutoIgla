<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 animate-fade-in-down">
    <div class="max-w-md w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden border-t-4 border-orange-500">
        
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fa-solid fa-box-open mr-2 text-orange-500"></i>
                {{ $id_articulo ? 'Editar Artículo' : 'Nuevo Artículo' }}
            </h2>
            <button wire:click="closeModal" class="text-gray-400 hover:text-red-500 transition text-xl">&times;</button>
        </div>

        <form wire:submit.prevent="guardar" class="p-6 space-y-5" autocomplete="off">
            
            {{-- Categoría --}}
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Categoría *</label>
                <div class="flex gap-2">
                    <select wire:model="id_categoria_articulo" 
                        class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-white">
                        <option value="">Seleccione una categoría...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id_categoria_articulo }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="openCategoriaModal" 
                        class="bg-gray-100 text-gray-600 border border-gray-300 px-4 rounded-lg hover:bg-orange-50 hover:text-orange-600 hover:border-orange-300 transition-colors" title="Crear Categoría rápida">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                @error('id_categoria_articulo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Nombre --}}
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre del Artículo *</label>
                <input type="text" wire:model="nombre" placeholder="Ej: Chaqueta de Chef Talla M"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Precio --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Precio *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold">Bs</span>
                        </div>
                        <input type="number" step="0.50" wire:model="precio" 
                            class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 font-bold text-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                    </div>
                    @error('precio') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                {{-- Stock --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Stock Físico</label>
                    <input type="number" wire:model="stock" placeholder="{{ $es_servicio ? 'Ilimitado' : '0' }}"
                        class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow 
                        {{ $es_servicio ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : '' }}" {{ $es_servicio ? 'disabled' : '' }}>
                    @error('stock') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    
                    {{-- Checkbox Es Servicio --}}
                    <div class="mt-2 flex items-center gap-2">
                        <input type="checkbox" wire:model.live="es_servicio" id="checkServicio" 
                            class="w-4 h-4 text-orange-500 bg-gray-100 border-gray-300 rounded focus:ring-orange-500">
                        <label for="checkServicio" class="text-xs font-bold text-blue-600 cursor-pointer select-none hover:text-blue-800">
                            Es un Servicio (Sin Stock)
                        </label>
                    </div>
                </div>
            </div>

            {{-- Checkbox Obligatorio --}}
            <div class="mt-2 flex items-start gap-3 bg-red-50 p-3 rounded-lg border border-red-100 hover:bg-red-100 transition-colors">
                <div class="flex items-center h-5">
                    <input type="checkbox" wire:model="es_obligatorio" id="checkObligatorio" 
                        class="w-5 h-5 text-red-600 bg-white border-red-300 rounded focus:ring-red-500 cursor-pointer">
                </div>
                <div class="flex flex-col">
                    <label for="checkObligatorio" class="text-sm font-bold text-red-800 cursor-pointer select-none">
                        Cobro Obligatorio
                    </label>
                    <p class="text-[11px] text-red-600">Al marcar esto, el sistema forzará la compra de este ítem en mensualidades o semanales (Ej: Derecho de examen).</p>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                
                {{-- Spinner anti-doble clic --}}
                <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="guardar"
                    class="px-5 py-2.5 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    
                    <span wire:loading.remove wire:target="guardar">
                        <i class="fa-solid fa-check"></i> {{ $id_articulo ? 'Guardar Cambios' : 'Registrar' }}
                    </span>

                    <span wire:loading wire:target="guardar">
                        <i class="fa-solid fa-spinner fa-spin"></i> Guardando...
                    </span>
                </button>
            </div>
            
        </form>
    </div>
</div>