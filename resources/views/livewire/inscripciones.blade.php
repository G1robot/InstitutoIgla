<div class="px-4">
    
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">INSCRIPCIONES GENERALES</h2>
        <p class="text-sm text-gray-500 mt-1">Gestiona los planes de estudio y el estado académico de los estudiantes.</p>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        
        <button wire:click="openModal" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-file-signature"></i> Nueva Inscripción
        </button>

        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" 
                placeholder="Buscar estudiante, plan o gestión..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 focus:bg-white text-sm">
        </div>

    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Estudiante</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Plan Académico</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Gestión Inicio</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Cambiar Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($inscripciones as $i)
                        <tr wire:key="ins-{{ $i->id_inscripcion }}" class="hover:bg-orange-50 transition-colors group">
                            
                            {{-- Estudiante --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900 flex items-center gap-2 text-base">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                        <i class="fa-solid fa-user-graduate"></i>
                                    </div>
                                    {{ $i->estudiante->nombre }} {{ $i->estudiante->apellido }}
                                </div>
                            </td>

                            {{-- Plan --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-gray-50 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-gray-200 flex items-center gap-2 w-max">
                                    <span><i class="fa-solid fa-layer-group text-orange-500 mr-1"></i> {{ $i->plan->nombre }}</span>
                                    <span class="text-gray-300">|</span>
                                    <span class="text-gray-500"><i class="fa-regular fa-clock mr-1"></i> {{ $i->turno?->nombre ?? 'Sin turno' }}</span>
                                </span>
                            </td>

                            {{-- Gestión e Inicio --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-gray-800 font-bold">{{ $i->gestion_inicio }}</div>
                            </td>

                            {{-- Estado (Badges Visuales) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($i->estado === 'activo')
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200">
                                        <i class="fa-solid fa-circle-check mr-1"></i> ACTIVO
                                    </span>
                                @elseif($i->estado === 'retirado')
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold border border-red-200">
                                        <i class="fa-solid fa-circle-xmark mr-1"></i> RETIRADO
                                    </span>
                                @elseif($i->estado === 'egresado')
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold border border-blue-200">
                                        <i class="fa-solid fa-graduation-cap mr-1"></i> EGRESADO
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold border border-gray-200 uppercase">
                                        {{ $i->estado }}
                                    </span>
                                @endif
                            </td>

                            {{-- Acciones Rápidas (Iconos en vez de botones gigantes) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center gap-2">
                                    {{-- Botón Cambiar Turno --}}
                                    <button wire:click="abrirModalTurno({{ $i->id_inscripcion }})" 
                                        class="bg-orange-50 text-orange-500 hover:bg-orange-500 hover:text-white px-3 py-1.5 rounded-lg transition border border-orange-100 shadow-sm text-xs font-bold" title="Cambiar Turno">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </button>
                                    @if($i->estado === 'activo')
                                        <button wire:click="retirar({{ $i->id_inscripcion }})" 
                                            onclick="confirm('¿Estás seguro de marcar a este estudiante como Retirado?') || event.stopImmediatePropagation()"
                                            class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1.5 rounded-lg transition border border-red-100 shadow-sm text-xs font-bold" title="Retirar">
                                            <i class="fa-solid fa-user-minus"></i>
                                        </button>
                                    @elseif($i->estado === 'retirado')
                                        <button wire:click="activar({{ $i->id_inscripcion }})"
                                            class="bg-green-50 text-green-600 hover:bg-green-600 hover:text-white px-3 py-1.5 rounded-lg transition border border-green-100 shadow-sm text-xs font-bold" title="Reactivar">
                                            <i class="fa-solid fa-user-check"></i>
                                        </button>
                                    @endif

                                    @if($i->estado !== 'anulado')
                                        <button wire:click="anularPorError({{ $i->id_inscripcion }})"
                                            onclick="confirm('¿Estás seguro de ANULAR esta inscripción? Si ya se cobró dinero, se descontará del arqueo y deberás devolverlo al estudiante.') || event.stopImmediatePropagation()"
                                            class="bg-gray-50 text-gray-500 hover:bg-red-600 hover:text-white px-3 py-1.5 rounded-lg transition border border-gray-200 shadow-sm text-xs font-bold" title="Anular por Error">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @endif

                                    @if($i->estado !== 'egresado')
                                        <button wire:click="egresado({{ $i->id_inscripcion }})"
                                            onclick="confirm('¿Confirmar que este estudiante ya egresó del plan?') || event.stopImmediatePropagation()"
                                            class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg transition border border-blue-100 shadow-sm text-xs font-bold" title="Marcar como Egresado">
                                            <i class="fa-solid fa-graduation-cap"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-300 mb-3"><i class="fa-solid fa-file-circle-xmark text-4xl"></i></div>
                                <p class="text-gray-500">No se encontraron inscripciones registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($inscripciones, 'hasPages') && $inscripciones->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $inscripciones->links() }}
            </div>
        @endif
    </div>

    @if($showModal)
        @include('livewire.inscripcionesModal')
    @endif
    {{-- MODAL CAMBIAR TURNO --}}
    @if($showModalTurno)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 animate-fade-in-down">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden border-t-4 border-orange-500">
            
            {{-- Cabecera del Modal --}}
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-orange-50">
                <h3 class="font-black text-orange-800 text-lg flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-orange-500"></i> Cambiar Turno
                </h3>
                <button wire:click="cerrarModalTurno" class="text-orange-400 hover:text-orange-700 text-xl transition">&times;</button>
            </div>
            
            {{-- Cuerpo del Modal --}}
            <div class="p-6">
                <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Seleccione el Nuevo Turno</label>
                <select wire:model="nuevo_id_turno" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow text-gray-700 font-bold bg-gray-50">
                    <option value="">Seleccione...</option>
                    @foreach($turnos as $t)
                        <option value="{{ $t->id_turno }}">{{ $t->nombre }}</option>
                    @endforeach
                </select>
                @error('nuevo_id_turno') <span class="text-red-500 text-xs mt-1 block"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> @enderror
            </div>
            
            {{-- Pie del Modal (Botones) --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <button wire:click="cerrarModalTurno" class="px-4 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                    Cancelar
                </button>
                <button wire:click="actualizarTurno" wire:loading.attr="disabled" wire:target="actualizarTurno" class="px-4 py-2 text-sm font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-lg shadow-md flex items-center gap-2 transition">
                    <span wire:loading.remove wire:target="actualizarTurno"><i class="fa-solid fa-save"></i> Guardar Cambio</span>
                    <span wire:loading wire:target="actualizarTurno"><i class="fa-solid fa-spinner fa-spin"></i> Guardando...</span>
                </button>
            </div>
            
        </div>
    </div>
    @endif

</div>