<div>
    @if($necesitaApertura)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-80 backdrop-blur-md z-[100] animate-fade-in-down">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 relative border-t-4 border-orange-500">
                
                <form action="{{ route('logout') }}" method="POST" class="absolute top-5 right-5">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors flex items-center gap-1.5 text-xs font-bold bg-gray-50 hover:bg-red-50 px-3 py-1.5 rounded-full" title="Cerrar Sesión">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Salir
                    </button>
                </form>

                <div class="text-center mb-8 mt-2">
                    <div class="mx-auto w-16 h-16 bg-orange-50 border border-orange-100 rounded-full flex items-center justify-center mb-4 shadow-sm">
                        <i class="fas fa-cash-register text-2xl text-orange-500"></i>
                    </div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Apertura de Caja</h2>
                    <p class="text-gray-500 text-sm mt-2 leading-tight">
                        ¡Hola, <span class="font-bold text-gray-700">{{ Auth::user()->nombre }}</span>!<br>
                        Abre tu turno para comenzar a operar.
                    </p>
                </div>

                <form wire:submit.prevent="abrirCaja" class="space-y-6" autocomplete="off">
                    
                    <div>
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Turno de Trabajo *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fa-regular fa-clock text-gray-400"></i>
                            </div>
                            <select wire:model="id_turno" class="w-full pl-11 pr-10 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow appearance-none cursor-pointer">
                                <option value="">Seleccione su turno...</option>
                                @foreach($turnos as $turno)
                                    <option value="{{ $turno->id_turno }}">{{ $turno->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                            </div>
                        </div>
                        @error('id_turno') <span class="text-red-500 text-xs font-bold mt-1.5 block"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-2">Monto Inicial (Caja Chica) *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-400 font-bold">Bs</span>
                            </div>
                            <input type="number" step="0.50" wire:model="monto_inicial" 
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xl font-black text-gray-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-shadow shadow-inner" 
                                placeholder="0.00" autocomplete="off">
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2 font-medium leading-tight">Registra el dinero físico exacto (cambio) con el que inicia tu gaveta hoy.</p>
                        @error('monto_inicial') <span class="text-red-500 text-xs font-bold mt-1.5 block"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2 border-t border-gray-100">
                        <button type="submit" 
                            wire:loading.attr="disabled"
                            wire:target="abrirCaja"
                            class="w-full bg-gray-900 hover:bg-black text-white font-bold py-4 rounded-xl shadow-[0_10px_20px_rgba(0,0,0,0.2)] transition-all active:scale-95 flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            
                            <span wire:loading.remove wire:target="abrirCaja" class="flex items-center gap-2">
                                <i class="fas fa-lock-open text-orange-500"></i> Abrir Caja y Comenzar
                            </span>
                            
                            <span wire:loading wire:target="abrirCaja" class="flex items-center gap-2 text-orange-500">
                                <i class="fa-solid fa-spinner fa-spin"></i> Registrando apertura...
                            </span>
                        </button>
                    </div>
                    
                </form>

            </div>
        </div>
    @endif
</div>