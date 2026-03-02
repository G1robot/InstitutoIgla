<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 animate-fade-in-down">
    <div class="max-w-2xl w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden">
        
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fa-solid fa-user-pen mr-2 text-orange-500"></i>
                {{ $estudiante_id ? 'Editar Estudiante' : 'Nuevo Estudiante' }}
            </h2>
            <button wire:click="closeModal" class="text-gray-400 hover:text-red-500 transition text-xl">&times;</button>
        </div>

        <form wire:submit.prevent="enviarClick" class="p-6" autocomplete="off">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombres *</label>
                    <input wire:model="nombre" type="text" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                    @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Apellidos *</label>
                    <input wire:model="apellido" type="text" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                    @error('apellido') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Cédula de Identidad *</label>
                    <input wire:model="ci" type="text" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                    @error('ci') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Teléfono / Celular *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-phone text-gray-400 text-xs"></i>
                        </div>
                        <input wire:model="telefono" type="text" class="w-full pl-9 border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                    </div>
                    @error('telefono') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Género *</label>
                    <select wire:model="genero" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-white">
                        <option value="">Seleccione...</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                    </select>
                    @error('genero') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">F. de Nacimiento *</label>
                    <input wire:model="fecha_nacimiento" type="date" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow text-gray-700">
                    @error('fecha_nacimiento') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="enviarClick"
                    class="px-5 py-2.5 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    
                    {{-- Lo que se muestra cuando NO está cargando --}}
                    <span wire:loading.remove wire:target="enviarClick" class="flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> {{ $estudiante_id ? 'Guardar Cambios' : 'Registrar' }}
                    </span>

                    {{-- Lo que se muestra cuando SÍ está cargando --}}
                    <span wire:loading wire:target="enviarClick" class="flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin"></i> Procesando...
                    </span>
                </button>
            </div>
            
        </form>
    </div>
</div>