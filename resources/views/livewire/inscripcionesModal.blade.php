<div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm z-50 animate-fade-in-down">
    <div class="max-w-md w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-visible border-t-4 border-orange-500">

        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center rounded-t-2xl">
            <h2 class="text-lg font-bold text-gray-800">
                <i class="fa-solid fa-file-signature mr-2 text-orange-500"></i>
                Nueva Inscripción General
            </h2>
            <button wire:click="closeModal" class="text-gray-400 hover:text-red-500 transition text-xl">&times;</button>
        </div>

        <form wire:submit.prevent="guardar" class="p-6 space-y-5" autocomplete="off">
            
            {{-- Buscar estudiante (CON REACTIVIDAD EN TIEMPO REAL) --}}
            <div class="relative">
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Estudiante Seleccionado *</label>
                
                @if($id_estudiante)
                    {{-- 1. VISTA DE ÉXITO (Se oculta el input y sale esta tarjeta verde) --}}
                    <div class="w-full flex items-center justify-between border-2 border-green-500 bg-green-50 rounded-lg p-2.5 shadow-sm transition-all animate-fade-in-down">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-circle-check text-green-500 text-lg shadow-sm rounded-full bg-white"></i>
                            <span class="font-bold text-green-800">{{ $searchEstudiante }}</span>
                        </div>
                        <button type="button" wire:click="limpiarEstudiante" class="text-red-400 hover:text-red-600 transition bg-white rounded-full px-2 py-1 shadow-sm" title="Cambiar estudiante">
                            <i class="fa-solid fa-xmark font-bold"></i> Cancelar
                        </button>
                    </div>
                @else
                    {{-- 2. VISTA DE BÚSQUEDA NORMAL --}}
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user-magnifying-glass text-gray-400"></i>
                        </div>
                        
                        <input type="text" wire:model.live.debounce.300ms="searchEstudiante" 
                            class="w-full pl-10 border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-white"
                            placeholder="Escribe el nombre o CI para buscar...">
                    </div>
                    
                    {{-- Lista desplegable --}}
                    @if(!empty($estudiantes_encontrados))
                        <ul class="absolute z-50 w-full bg-white border border-orange-300 rounded-lg mt-1 shadow-2xl max-h-48 overflow-auto divide-y divide-gray-100 animate-fade-in-down">
                            @foreach($estudiantes_encontrados as $e)
                                <li wire:click="seleccionarEstudiante({{ $e->id_estudiante }})" 
                                    class="p-3 cursor-pointer hover:bg-orange-50 transition flex items-center gap-3 text-sm">
                                    <i class="fa-solid fa-chevron-right text-orange-400 text-xs"></i>
                                    <div>
                                        <span class="font-bold text-gray-800">{{ $e->nombre }} {{ $e->apellido }}</span> 
                                        <span class="text-gray-500 text-xs block">CI: {{ $e->ci }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
                
                @error('id_estudiante') <span class="text-red-500 text-xs mt-1 block"><i class="fa-solid fa-circle-exclamation"></i> Selecciona un estudiante válido de la lista.</span> @enderror
            </div>

            {{-- Plan --}}
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Plan Académico *</label>
                <select wire:model="id_plan" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-white font-medium text-gray-700">
                    <option value="">Seleccione un plan...</option>
                    @foreach($planes as $plan)
                        <option value="{{ $plan->id_plan }}">{{ $plan->nombre }} ({{ ucfirst($plan->tipo_pago) }})</option>
                    @endforeach
                </select>
                @error('id_plan') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                {{-- Gestión --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Gestión Inicio</label>
                    <input type="number" wire:model="gestion_inicio" placeholder="{{ date('Y') }}"
                        class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                    @error('gestion_inicio') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Turno --}}
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Turno *</label>
                    <select wire:model="id_turno" class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-white font-medium text-gray-700">
                        <option value="">Seleccione un turno...</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno->id_turno }}">{{ $turno->nombre }}</option>
                        @endforeach
                    </select>
                    @error('id_turno') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>


                {{-- Año actual --}}
                <div class="hidden">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Año Actual (Curso)</label>
                    <input type="number" wire:model="anio_actual" placeholder="1"
                        class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                    @error('anio_actual') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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
                        <i class="fa-solid fa-check"></i> Registrar Inscripción
                    </span>

                    <span wire:loading wire:target="guardar">
                        <i class="fa-solid fa-spinner fa-spin"></i> Guardando...
                    </span>
                </button>
            </div>

        </form>
    </div>
</div>