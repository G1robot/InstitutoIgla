<div class="px-4">
    
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">MÓDULOS DE ESTUDIO</h2>
        <p class="text-sm text-gray-500 mt-1">Administra los módulos, sus categorías y costos.</p>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        
        <button wire:click="openModal()" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-cube"></i> Nuevo Módulo
        </button>

        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" 
                placeholder="Buscar módulo..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 focus:bg-white text-sm">
        </div>

    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Nombre del Módulo</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Costo</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($modulos as $item)
                        <tr class="hover:bg-orange-50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900 text-base">
                                    {{ $item->nombre }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->categoria)
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold border border-gray-200">
                                        <i class="fa-solid fa-layer-group mr-1 text-orange-400"></i> {{ $item->categoria->nombre }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic text-xs">Sin Categoría</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="font-black text-orange-600">{{ number_format($item->costo, 2) }}</span> 
                                <span class="text-xs font-bold text-gray-400">Bs</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button wire:click.prevent="editar({{$item->id_modulo}})" 
                                    class="text-gray-400 hover:text-orange-600 transition p-2 rounded-lg hover:bg-orange-100" title="Editar Módulo">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="text-gray-300 mb-3"><i class="fa-solid fa-cubes text-4xl"></i></div>
                                <p class="text-gray-500">No hay módulos registrados o no coinciden con la búsqueda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($modulos, 'hasPages') && $modulos->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $modulos->links() }}
            </div>
        @endif
    </div>

    @if($showModal)
        @include('livewire.ModulosModal')
    @endif

    @if($showCategoriaModal)
        @include('livewire.CategoriaModal')
    @endif
</div>