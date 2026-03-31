<div class="container mx-auto px-4 py-8 max-w-5xl">
    
    <div class="mb-8 border-l-4 border-orange-500 pl-4">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">MI PERFIL</h2>
        <p class="text-sm text-gray-500 mt-1">Personaliza tu foto y actualiza tus credenciales de acceso.</p>
    </div>

    <form wire:submit.prevent="guardarPerfil" class="grid grid-cols-1 md:grid-cols-3 gap-8" autocomplete="off">
        
        {{-- COLUMNA IZQUIERDA: FOTO DE PERFIL --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                <h3 class="text-xs font-black text-gray-400 tracking-wider border-b pb-2 mb-6">FOTO DE PERFIL</h3>
                
                <div class="relative w-40 h-40 mx-auto mb-4 group">
                    {{-- Vista previa de la nueva foto o la actual --}}
                    @if ($foto)
                        <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full rounded-full object-cover shadow-md border-4 border-gray-50">
                    @elseif ($fotoActual)
                        <img src="{{ asset('storage/' . $fotoActual) }}" class="w-full h-full rounded-full object-cover shadow-md border-4 border-gray-50">
                    @else
                        <div class="w-full h-full rounded-full bg-gray-100 text-gray-400 flex items-center justify-center shadow-md border-4 border-gray-50">
                            <i class="fa-solid fa-user text-6xl"></i>
                        </div>
                    @endif

                    {{-- Botón flotante para subir --}}
                    <label class="absolute bottom-0 right-2 bg-orange-500 hover:bg-orange-600 text-white w-10 h-10 rounded-full flex items-center justify-center cursor-pointer shadow-lg transition-transform hover:scale-110">
                        <i class="fa-solid fa-camera"></i>
                        <input type="file" wire:model="foto" class="hidden" accept="image/*">
                    </label>
                </div>
                
                <div wire:loading wire:target="foto" class="text-orange-500 text-xs font-bold mb-2">
                    <i class="fa-solid fa-spinner fa-spin"></i> Cargando imagen...
                </div>
                
                <p class="text-[10px] text-gray-400 uppercase">Formatos: JPG, PNG. Max: 2MB.</p>
                @error('foto') <span class="text-red-500 text-xs mt-1 block font-bold">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- COLUMNA DERECHA: DATOS DEL FORMULARIO --}}
        <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div class="sm:col-span-2">
                    <h3 class="text-xs font-black text-gray-400 tracking-wider border-b pb-2 mb-4">DATOS PERSONALES</h3>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre Completo *</label>
                    <input wire:model="nombre" type="text" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow text-gray-800 font-bold">
                    @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Teléfono / Celular *</label>
                    <input wire:model="telefono" type="text" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow text-gray-800 font-bold">
                    @error('telefono') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div class="sm:col-span-2">
                    <h3 class="text-xs font-black text-gray-400 tracking-wider border-b pb-2 mb-4">CREDENCIALES DE ACCESO</h3>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre de Usuario *</label>
                    <input wire:model="usuario" type="text" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 font-bold">
                    @error('usuario') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="hidden sm:block">
                    {{-- Espacio vacío para alinear la grilla --}}
                </div>

                <div class="bg-orange-50 p-4 rounded-xl border border-orange-100 sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <p class="text-[10px] text-orange-600 uppercase font-black tracking-wider"><i class="fa-solid fa-lock"></i> Cambio de Contraseña (Opcional)</p>
                        <p class="text-xs text-gray-500 mt-1">Déjalo en blanco si no deseas cambiar tu contraseña actual.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nueva Contraseña</label>
                        <input wire:model="contrasena" type="password" class="w-full border border-white rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 shadow-sm">
                        @error('contrasena') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Confirmar Nueva Contraseña</label>
                        <input wire:model="contrasena1" type="password" class="w-full border border-white rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 shadow-sm">
                        @error('contrasena1') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" wire:loading.attr="disabled" class="px-8 py-3 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-xl shadow-md transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="guardarPerfil"><i class="fa-solid fa-save"></i> Guardar Cambios</span>
                    <span wire:loading wire:target="guardarPerfil"><i class="fa-solid fa-spinner fa-spin"></i> Guardando...</span>
                </button>
            </div>

        </div>
    </form>
</div>