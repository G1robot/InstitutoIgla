<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 animate-fade-in-down">
    <div class="max-w-3xl w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden">
        
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fa-solid fa-user-shield mr-2 text-orange-500"></i>
                {{ $usuario_id ? 'Editar Usuario' : 'Nuevo Usuario' }}
            </h2>
            <button wire:click="closeModal" class="text-gray-400 hover:text-red-500 transition text-xl">&times;</button>
        </div>

        {{-- Agregamos autocomplete="off" para evitar sugerencias molestas --}}
        <form wire:submit.prevent="enviarClick" class="p-6" autocomplete="off">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="space-y-5">
                    <h3 class="text-xs font-black text-gray-400 tracking-wider border-b pb-2">DATOS PERSONALES</h3>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre Completo *</label>
                        <input wire:model="nombre" type="text" autocomplete="off"
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                        @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Teléfono / Celular *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-phone text-gray-400 text-xs"></i>
                            </div>
                            <input wire:model="telefono" type="text" autocomplete="off"
                                class="w-full pl-9 border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                        </div>
                        @error('telefono') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Rol del Sistema *</label>
                        <select wire:model="rol" 
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-white">
                            <option value="">Seleccione un rol...</option>
                            <option value="personal">Personal (Cajero)</option>
                            <option value="administrador">Administrador</option>
                        </select>
                        @error('rol') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="space-y-5">
                    <h3 class="text-xs font-black text-gray-400 tracking-wider border-b pb-2">CREDENCIALES DE ACCESO</h3>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre de Usuario *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-at text-gray-400 text-xs"></i>
                            </div>
                            <input wire:model="usuario" type="text" autocomplete="off"
                                class="w-full pl-9 border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                        </div>
                        @error('usuario') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">
                            Contraseña {{ $usuario_id ? '(Dejar en blanco para no cambiar)' : '*' }}
                        </label>
                        <input wire:model="contrasena" type="password" autocomplete="new-password"
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                        @error('contrasena') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Confirmar Contraseña</label>
                        <input wire:model="contrasena1" type="password" autocomplete="new-password"
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                        @error('contrasena1') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 pt-5">
                <button type="button" wire:click="closeModal" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                
                {{-- Botón con estado de carga (Spinner anti-doble clic) --}}
                <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="enviarClick"
                    class="px-5 py-2.5 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-sm flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    
                    <span wire:loading.remove wire:target="enviarClick" class="flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> {{ $usuario_id ? 'Guardar Cambios' : 'Registrar' }}
                    </span>

                    <span wire:loading wire:target="enviarClick" class="flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin"></i> Procesando...
                    </span>
                </button>
            </div>
            
        </form>
    </div>
</div>