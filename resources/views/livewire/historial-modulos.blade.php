<div class="container mx-auto px-6 py-8">

    <h2 class="text-center text-3xl font-bold mb-8 text-gray-800 border-b pb-4">
        HISTORIAL ACADÉMICO (MÓDULOS)
    </h2>

    {{-- 1. BUSCADOR DE ESTUDIANTE --}}
    <div class="max-w-3xl mx-auto mb-10 relative">
        <label class="block text-gray-700 text-sm font-bold mb-2">Buscar Estudiante:</label>
        <div class="relative">
            <input type="text" wire:model.live.debounce.300ms="search"
                class="w-full p-4 pl-12 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 text-lg"
                placeholder="Escribe nombre o CI...">
            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
        </div>

        {{-- Lista desplegable --}}
        @if(!empty($estudiantesEncontrados))
            <div class="absolute w-full bg-white shadow-xl border mt-1 z-20 rounded-lg overflow-hidden">
                @foreach($estudiantesEncontrados as $est)
                    <div wire:click="seleccionarEstudiante({{ $est->id_estudiante }})"
                        class="p-4 hover:bg-blue-50 cursor-pointer border-b last:border-0 flex justify-between items-center">
                        <div>
                            <span class="font-bold text-gray-800">{{ $est->nombre }} {{ $est->apellido }}</span>
                            <br>
                            <span class="text-sm text-gray-500">CI: {{ $est->ci }}</span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300"></i>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- 2. INFORMACIÓN Y LISTA DE MÓDULOS --}}
    @if($estudianteSeleccionado)
        
        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 mb-8 rounded shadow-sm flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-blue-900">
                    {{ $estudianteSeleccionado->nombre }} {{ $estudianteSeleccionado->apellido }}
                </h3>
                <p class="text-blue-700 text-sm">Estudiante Regular</p>
            </div>
            <div class="text-right">
                <span class="bg-white px-3 py-1 rounded border text-sm font-bold">
                    {{ count($modulos) }} Módulos Inscritos
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($modulos as $ins)
                @php
                    $esFinalizado = $ins->estado === 'finalizado';
                    $pago = $ins->pagos->first(); // Obtenemos el pago relacionado
                @endphp

                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden relative group hover:shadow-2xl transition duration-300">
                    
                    {{-- Barra de estado superior --}}
                    <div class="h-2 w-full {{ $esFinalizado ? 'bg-green-500' : 'bg-blue-500' }}"></div>

                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-xs font-bold px-2 py-1 rounded uppercase tracking-wider
                                {{ $esFinalizado ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $ins->estado }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($ins->fecha_inscripcion)->format('d/m/Y') }}
                            </span>
                        </div>

                        <h4 class="text-lg font-bold text-gray-800 mb-1 leading-tight">
                            {{ $ins->modulo->nombre }}
                        </h4>
                        <p class="text-sm text-gray-500 mb-4">
                            Costo: {{ number_format($ins->costo_al_momento, 2) }} Bs
                        </p>

                        {{-- Información del Pago --}}
                        <div class="bg-gray-50 p-3 rounded-lg mb-4 text-sm">
                            <p class="font-semibold text-gray-600 flex items-center gap-2">
                                <i class="fas fa-receipt"></i> Estado de Pago:
                            </p>
                            @if($pago && $pago->estado == 'pagado')
                                <span class="text-green-600 font-bold block mt-1">
                                    <i class="fas fa-check"></i> Pagado Completo
                                </span>
                            @else
                                <span class="text-red-500 font-bold block mt-1">
                                    <i class="fas fa-exclamation-triangle"></i> Pendiente / Parcial
                                </span>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div class="border-t pt-4 mt-2 text-center">
                            @if(!$esFinalizado)
                                <button wire:click="marcarFinalizado({{ $ins->id_inscripcion_modulo }})" 
                                    class="w-full bg-green-600 text-white font-bold py-2 rounded shadow hover:bg-green-700 transition flex items-center justify-center gap-2">
                                    <i class="fas fa-graduation-cap"></i> Finalizar Módulo
                                </button>
                            @else
                                <div class="flex gap-2">
                                    <button class="flex-1 bg-gray-200 text-gray-600 font-bold py-2 rounded cursor-not-allowed" disabled>
                                        <i class="fas fa-check-double"></i> Aprobado
                                    </button>
                                    {{-- Botón opcional para reactivar si te equivocaste --}}
                                    <button wire:click="reactivarModulo({{ $ins->id_inscripcion_modulo }})" 
                                        class="bg-yellow-100 text-yellow-600 px-3 rounded hover:bg-yellow-200" title="Reactivar">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            @empty
                <div class="col-span-3 text-center py-12">
                    <div class="bg-gray-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book-open text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-600">Sin historial</h3>
                    <p class="text-gray-500">Este estudiante no tiene inscripciones a módulos registradas.</p>
                </div>
            @endforelse
        </div>

    @elseif($search == '')
        {{-- Mensaje inicial --}}
        <div class="text-center py-20 opacity-50">
            <i class="fas fa-user-graduate text-6xl text-gray-300 mb-4"></i>
            <h2 class="text-2xl font-bold text-gray-400">Seleccione un estudiante</h2>
            <p>Utilice el buscador para ver el historial académico.</p>
        </div>
    @endif
</div>