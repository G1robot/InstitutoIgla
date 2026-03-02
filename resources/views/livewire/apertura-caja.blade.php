<div>
    
    @if($necesitaApertura)
        <div class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-90 backdrop-blur-sm z-[100]">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 transform transition-all animate-bounce-in relative">
                
                <form action="{{ route('logout') }}" method="POST" class="absolute top-4 right-4">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold bg-transparent border-none cursor-pointer">
                        Salir
                    </button>
                </form>
                <div class="text-center mb-6">
                    <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-cash-register text-3xl text-blue-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Apertura de Caja</h2>
                    <p class="text-gray-500 text-sm mt-1">¡Hola, {{ Auth::user()->nombre }}! Para comenzar a registrar movimientos, debes abrir tu caja.</p>
                </div>

                <form wire:submit.prevent="abrirCaja" class="space-y-5">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Turno de Trabajo *</label>
                        <select wire:model="id_turno" class="w-full border-2 border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="">Seleccione su turno...</option>
                            @foreach($turnos as $turno)
                                <option value="{{ $turno->id_turno }}">{{ $turno->nombre }}</option>
                            @endforeach
                        </select>
                        @error('id_turno') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Monto Inicial (Base en Caja) *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Bs</span>
                            </div>
                            <input type="number" step="0.50" wire:model="monto_inicial" 
                                class="w-full pl-10 border-2 border-gray-300 rounded-lg p-3 text-lg font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                placeholder="0.00">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Dinero físico (cambio) con el que inicia el turno.</p>
                        @error('monto_inicial') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg transition transform active:scale-95 flex justify-center items-center gap-2">
                        <i class="fas fa-lock-open"></i> Iniciar Turno
                    </button>
                    
                </form>

            </div>
        </div>
    @endif
</div>