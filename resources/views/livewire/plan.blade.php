<div class="px-4">
    
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">PLANES DE INSCRIPCIÓN</h2>
        <p class="text-sm text-gray-500 mt-1">Configura las modalidades de estudio, duraciones y costos principales.</p>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        
        <button wire:click="openModal()" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-layer-group"></i> Nuevo Plan
        </button>

        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" 
                placeholder="Buscar plan..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 focus:bg-white text-sm">
        </div>

    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Nombre del Plan</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Duración Total</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Costo Anual</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Costo Mensual</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Modalidad</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($planes as $item)
                        <tr class="hover:bg-orange-50 transition-colors group">
                            
                            {{-- Nombre --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <i class="fa-solid fa-star text-orange-400 text-xs"></i>
                                    {{ $item->nombre }}
                                </div>
                            </td>

                            {{-- Duración --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-600">
                                @if($item->duracion_anios > 0)
                                    <span class="font-bold">{{ $item->duracion_anios }}</span> {{ $item->duracion_anios == 1 ? 'Año' : 'Años' }}
                                @endif
                                @if($item->duracion_meses > 0)
                                    @if($item->duracion_anios > 0) y @endif
                                    <span class="font-bold">{{ $item->duracion_meses }}</span> {{ $item->duracion_meses == 1 ? 'Mes' : 'Meses' }}
                                @endif
                                @if(empty($item->duracion_anios) && empty($item->duracion_meses))
                                    <span class="text-gray-400 italic">No definida</span>
                                @endif
                            </td>

                            {{-- Costo Anual --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @if($item->costo_anual)
                                    <span class="font-black text-gray-800">{{ number_format($item->costo_anual, 2) }}</span> <span class="text-xs text-gray-400">Bs</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>

                            {{-- Costo Mensual --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @if($item->costo_mensual)
                                    <span class="font-black text-orange-600">{{ number_format($item->costo_mensual, 2) }}</span> <span class="text-xs text-orange-400">Bs</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>

                            {{-- Modalidad (Tipo de Pago) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if(strtolower($item->tipo_pago) == 'anual')
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold border border-blue-200">
                                        ANUAL
                                    </span>
                                @else
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200">
                                        MENSUAL
                                    </span>
                                @endif
                            </td>

                            {{-- Acción --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button wire:click.prevent="editar({{$item->id_plan}})" 
                                    class="text-gray-400 hover:text-orange-600 transition p-2 rounded-lg hover:bg-orange-100" title="Editar Plan">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-300 mb-3"><i class="fa-solid fa-layer-group text-4xl"></i></div>
                                <p class="text-gray-500">No hay planes registrados o no coinciden con la búsqueda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showModal)
        @include('livewire.planModal')
    @endif
</div>