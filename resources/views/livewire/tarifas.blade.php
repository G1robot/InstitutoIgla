<div class="px-4">
    
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">TARIFAS Y ARANCELES</h2>
        <p class="text-sm text-gray-500 mt-1">Configuración de los costos de planes de inscripción vigentes.</p>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        
        <button wire:click="openModal()" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-tag"></i> Nueva Tarifa
        </button>

        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" 
                placeholder="Buscar código o año..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 focus:bg-white text-sm">
        </div>

    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Código de Tarifa</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Gestión (Año)</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Monto a Cobrar</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($tarifas as $item)
                        <tr class="hover:bg-orange-50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold text-gray-800 bg-gray-100 px-3 py-1 rounded-md border border-gray-200">
                                    {{ $item->codigo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->gestion)
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold border border-gray-200">
                                        <i class="fa-regular fa-calendar mr-1"></i> {{ $item->gestion }}
                                    </span>
                                @else
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold border border-blue-200">
                                        <i class="fa-solid fa-infinity mr-1 text-[10px]"></i> Permanente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-orange-600 font-black text-base">
                                {{ number_format($item->monto, 2) }} <span class="text-xs font-bold text-gray-400">Bs</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button wire:click.prevent="editar({{$item->id_tarifa}})" 
                                    class="text-gray-400 hover:text-orange-600 transition p-2 rounded-lg hover:bg-orange-100" title="Editar Tarifa">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="text-gray-300 mb-3"><i class="fa-solid fa-tags text-4xl"></i></div>
                                <p class="text-gray-500">No hay tarifas registradas o no coinciden con la búsqueda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showModal)
        @include('livewire.TarifasModal')
    @endif
</div>