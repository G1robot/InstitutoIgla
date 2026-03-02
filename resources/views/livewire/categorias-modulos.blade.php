<div class="px-4">
    
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">CATEGORÍAS DE MÓDULOS</h2>
        <p class="text-sm text-gray-500 mt-1">Gestiona las familias o grupos de los módulos de estudio.</p>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        <button wire:click="openModal()" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-folder-plus"></i> Nueva Categoría
        </button>

        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" 
                placeholder="Buscar categoría..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 focus:bg-white text-sm">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-4xl">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider w-16">ID</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Nombre de la Categoría</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider w-32">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($categorias as $item)
                        <tr class="hover:bg-orange-50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-400 font-mono text-xs">
                                #{{ $item->id_categoria_modulo }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-800 flex items-center gap-2">
                                    <i class="fa-solid fa-folder text-orange-400"></i>
                                    {{ $item->nombre }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right flex justify-end gap-1">
                                {{-- Editar --}}
                                <button wire:click.prevent="editar({{$item->id_categoria_modulo}})" 
                                    class="text-gray-400 hover:text-orange-600 transition p-2 rounded-lg hover:bg-orange-100" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                
                                {{-- Eliminar con confirmación de JS nativo antes de ir a Livewire --}}
                                <button wire:click.prevent="eliminar({{$item->id_categoria_modulo}})" 
                                    onclick="confirm('¿Estás seguro de intentar eliminar esta categoría?') || event.stopImmediatePropagation()"
                                    class="text-gray-400 hover:text-red-600 transition p-2 rounded-lg hover:bg-red-50" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <div class="text-gray-300 mb-3"><i class="fa-solid fa-folder-open text-4xl"></i></div>
                                <p class="text-gray-500">No hay categorías registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($categorias, 'hasPages') && $categorias->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $categorias->links() }}
            </div>
        @endif
    </div>

    @if($showModal)
        @include('livewire.categoriasModulosModal')
    @endif
</div>