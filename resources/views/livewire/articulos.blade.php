<div class="px-4">
    
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">ARTÍCULOS E INSUMOS</h2>
        <p class="text-sm text-gray-500 mt-1">Gestiona el inventario, precios y servicios complementarios.</p>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        
        <button wire:click="openModal()" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-box-open"></i> Nuevo Artículo
        </button>

        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" 
                placeholder="Buscar artículo..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 focus:bg-white text-sm">
        </div>

    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Nombre del Artículo</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Precio</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($articulos as $art)
                        <tr class="hover:bg-orange-50 transition-colors group">
                            
                            {{-- Nombre --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <i class="fa-solid {{ is_null($art->stock) ? 'fa-bell-concierge text-blue-400' : 'fa-box text-orange-400' }} text-sm w-4 text-center"></i>
                                    {{ $art->nombre }}
                                </div>
                            </td>

                            {{-- Categoría --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-bold border border-gray-200">
                                    {{ $art->categoria->nombre ?? 'Sin Categoría' }}
                                </span>
                            </td>

                            {{-- Precio --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="font-black text-orange-600">{{ number_format($art->precio, 2) }}</span> 
                                <span class="text-xs font-bold text-gray-400">Bs</span>
                            </td>

                            {{-- Stock --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if(is_null($art->stock))
                                    <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs font-bold border border-blue-100">
                                        <i class="fa-solid fa-infinity text-[10px] mr-1"></i> SERVICIO
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded text-sm font-black border {{ $art->stock < 5 ? 'bg-red-50 text-red-600 border-red-200' : 'bg-green-50 text-green-700 border-green-200' }}">
                                        {{ $art->stock }} <span class="text-xs font-normal">u.</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Tipo (Obligatorio/Opcional) --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($art->es_obligatorio)
                                    <span class="text-xs text-red-600 font-bold px-2 py-1 rounded bg-red-50 border border-red-200" title="Cobro forzado">
                                        OBLIGATORIO
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 font-bold" title="Compra opcional">
                                        Opcional
                                    </span>
                                @endif
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button wire:click.prevent="editar({{ $art->id_articulo }})" 
                                    class="text-gray-400 hover:text-orange-600 transition p-2 rounded-lg hover:bg-orange-100 mr-1" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button wire:click.prevent="eliminar({{ $art->id_articulo }})" 
                                    onclick="confirm('¿Estás seguro de eliminar este artículo?') || event.stopImmediatePropagation()"
                                    class="text-gray-400 hover:text-red-600 transition p-2 rounded-lg hover:bg-red-50" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-300 mb-3"><i class="fa-solid fa-boxes-stacked text-4xl"></i></div>
                                <p class="text-gray-500">No hay artículos registrados o no coinciden con la búsqueda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($articulos, 'hasPages') && $articulos->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $articulos->links() }}
            </div>
        @endif
    </div>

    @if($showModal)
        @include('livewire.ArticulosModal')
    @endif

    {{-- MODAL SECUNDARIO (NUEVA CATEGORÍA) --}}
    @if($showCategoriaModal)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-70 backdrop-blur-sm z-[60] animate-fade-in-down">
            <div class="max-w-sm w-full mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden border-2 border-orange-200">
                
                <div class="bg-orange-50 px-5 py-3 border-b border-orange-100 flex justify-between items-center">
                    <h3 class="text-md font-bold text-orange-800">
                        <i class="fa-solid fa-bolt text-orange-500 mr-2"></i>Creación Rápida
                    </h3>
                    <button wire:click="closeCategoriaModal" class="text-orange-400 hover:text-orange-700 transition">&times;</button>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre de la Categoría</label>
                        <input type="text" wire:model="nueva_categoria_nombre" placeholder="Ej: Uniformes" 
                            class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow">
                        @error('nueva_categoria_nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 mt-4 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeCategoriaModal" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button type="button" wire:click="guardarCategoria" class="px-4 py-2 text-sm font-bold text-white bg-gray-800 hover:bg-black rounded-lg transition-colors flex items-center gap-2">
                            Crear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>