<div class="px-4">
    
    <div class="mb-8">
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">GESTIÓN DE USUARIOS</h2>
        <p class="text-sm text-gray-500 mt-1">Administra los accesos y roles del personal del instituto.</p>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
        
        <button wire:click="openModal()" class="w-full md:w-auto bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
        </button>

        <div class="relative w-full md:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" 
                placeholder="Buscar por nombre o usuario..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow bg-gray-50 focus:bg-white text-sm">
        </div>

    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Credenciales</th>
                        <th class="px-6 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($usuarios as $item)
                        <tr class="hover:bg-orange-50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900 flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    {{ $item->nombre }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <i class="fa-solid fa-phone text-xs text-gray-400 mr-1"></i> {{ $item->telefono }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-600 border border-gray-200">
                                    <i class="fa-solid fa-at text-orange-400 mr-1"></i>{{ $item->usuario }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($item->rol === 'administrador')
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold border border-red-200">
                                        ADMINISTRADOR
                                    </span>
                                @else
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold border border-blue-200">
                                        PERSONAL
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button wire:click.prevent="editar({{$item->id_usuario}})" 
                                    class="text-gray-400 hover:text-orange-600 transition p-2 rounded-lg hover:bg-orange-100" title="Editar Usuario">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-gray-300 mb-3"><i class="fa-solid fa-users-slash text-4xl"></i></div>
                                <p class="text-gray-500">No hay usuarios registrados o no coinciden con la búsqueda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($usuarios, 'hasPages') && $usuarios->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    @if($showModal)
        @include('livewire.usuarioModal')
    @endif
</div>