<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IGLA - ERP</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/flowbite@2.3.0/dist/flowbite.min.js"></script>

    @livewireStyles
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans text-gray-800">
    @livewire('apertura-caja')
    @livewire('cierre-caja')

    {{-- ========================================== --}}
    {{-- TOP NAVBAR (DARK MODE CON TEXTO RESALTADO) --}}
    {{-- ========================================== --}}
    <nav class="fixed top-0 z-50 w-full bg-gray-900 shadow-lg border-b border-gray-800 h-16">
        <div class="px-5 h-full flex justify-between items-center">

            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-orange-600 rounded-lg flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-utensils text-white text-sm"></i>
                </div>
                <span class="font-black tracking-widest text-white text-xl">IGLA<span class="text-orange-500">POS</span></span>
            </div>

            @auth('web')
            <div class="relative">
                <button data-dropdown-toggle="dropdown-user"
                    class="flex items-center gap-3 text-gray-300 font-bold hover:text-white transition px-3 py-1.5 rounded-lg hover:bg-gray-800 border border-transparent hover:border-gray-700">
                    <div class="w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center shadow-md">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                    <div class="text-left hidden md:block">
                        <p class="text-sm leading-tight text-white">{{ Auth::guard('web')->user()->nombre }}</p>
                        <p class="text-[10px] text-orange-400 uppercase tracking-widest">{{ Auth::user()->rol }}</p>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-gray-500 ml-1"></i>
                </button>

                <div id="dropdown-user" class="hidden absolute right-0 mt-2 w-56 bg-white shadow-xl rounded-xl border border-gray-100 overflow-hidden">
                    <ul class="py-2 text-sm">
                        <li class="px-2">
                            @if(Auth::user()->rol === 'administrador')
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition font-bold">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión
                                    </button>
                                </form>
                            @else
                                <button type="button" onclick="Livewire.dispatch('solicitarCierre')" class="w-full flex items-center gap-3 px-3 py-2 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition font-bold">
                                    <i class="fa-solid fa-cash-register"></i> Cerrar Turno y Salir
                                </button>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
            @endauth
        </div>
    </nav>

    {{-- ========================================== --}}
    {{-- SIDEBAR LATERAL                            --}}
    {{-- ========================================== --}}
    <aside class="fixed top-0 left-0 w-64 h-screen bg-white border-r border-gray-200 pt-16 shadow-sm z-40">
        <div class="h-full overflow-y-auto no-scrollbar px-3 py-6">

            <div class="text-center mb-8 px-4 mt-2">
                <img src="/img/LOGO_POTOSI_01.png" alt="Logo IGLA" class="w-full max-w-[150px] mx-auto drop-shadow-sm transition-transform hover:scale-105">
            </div>

            <ul class="space-y-1 text-sm font-medium">

                <li class="text-xs font-black text-gray-400 px-4 mb-2 tracking-wider">OPERACIÓN DIARIA</li>

                <li>
                    <a href="{{ route('pagos') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('pagos') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-orange-500' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-500' }}">
                        <i class="fa-solid fa-credit-card w-5 text-center {{ request()->routeIs('pagos') ? 'text-orange-500' : 'text-gray-400' }}"></i>
                        <span>Gestión de Pagos</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('venta-articulos') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('venta-articulos') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-orange-500' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-500' }}">
                        <i class="fa-solid fa-cart-shopping w-5 text-center {{ request()->routeIs('venta-articulos') ? 'text-orange-500' : 'text-gray-400' }}"></i>
                        <span>Punto de Venta</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('egresos') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('egresos') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-orange-500' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-500' }}">
                        <i class="fa-solid fa-money-check-dollar w-5 text-center {{ request()->routeIs('egresos') ? 'text-orange-500' : 'text-gray-400' }}"></i>
                        <span>Registro de Egresos</span>
                    </a>
                </li>

                <li class="text-xs font-black text-gray-400 px-4 mt-6 mb-2 tracking-wider border-t pt-4">GESTIÓN ACADÉMICA</li>

                <li>
                    <a href="{{ route('estudiantes') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('estudiantes') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-orange-500' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-500' }}">
                        <i class="fa-solid fa-user-graduate w-5 text-center {{ request()->routeIs('estudiantes') ? 'text-orange-500' : 'text-gray-400' }}"></i>
                        <span>Estudiantes</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('inscripciones') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('inscripciones') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-orange-500' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-500' }}">
                        <i class="fa-solid fa-user-plus w-5 text-center {{ request()->routeIs('inscripciones') ? 'text-orange-500' : 'text-gray-400' }}"></i>
                        <span>Inscripciones</span>
                    </a>
                </li>

                {{-- DESPLEGABLE: MÓDULOS --}}
                <li x-data="{ open: {{ request()->routeIs('modulos', 'categorias-modulos', 'inscripcion-modulo', 'historial-modulos') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-3 rounded-lg transition-all text-gray-600 hover:bg-gray-50 hover:text-orange-500 focus:outline-none">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-cubes w-5 text-center text-gray-400"></i>
                            <span class="font-medium">Módulos Especiales</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300" :class="{'rotate-180': open}"></i>
                    </button>
                    <ul x-show="open" x-collapse class="mt-1 space-y-1 pl-11">
                        <li>
                            <a href="{{ route('inscripcion-modulo') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('inscripcion-modulo') ? 'text-orange-600 font-bold' : 'text-gray-500 hover:text-orange-500 hover:bg-gray-50' }}">
                                • Inscribir a Módulo
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('modulos') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('modulos') ? 'text-orange-600 font-bold' : 'text-gray-500 hover:text-orange-500 hover:bg-gray-50' }}">
                                • Catálogo de Módulos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('historial-modulos') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('historial-modulos') ? 'text-orange-600 font-bold' : 'text-gray-500 hover:text-orange-500 hover:bg-gray-50' }}">
                                • Historial de Cursado
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- ========================================== --}}
                {{-- ÁREA RESTRINGIDA (SOLO ADMINISTRADORES)    --}}
                {{-- ========================================== --}}
                {{-- @if(Auth::user()->rol === 'administrador') --}}
                    
                    <li class="text-xs font-black text-gray-400 px-4 mt-8 mb-2 tracking-wider border-t pt-4">CONFIGURACIÓN / ADMIN</li>
                    
                    {{-- DESPLEGABLE: PLANES Y TARIFAS --}}
                    <li x-data="{ open: {{ request()->routeIs('planes', 'tarifas', 'categorias-modulos', 'articulos') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-3 rounded-lg transition-all text-gray-600 hover:bg-gray-50 hover:text-orange-500 focus:outline-none">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-gears w-5 text-center text-gray-400"></i>
                                <span class="font-medium">Parámetros del ERP</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300" :class="{'rotate-180': open}"></i>
                        </button>
                        <ul x-show="open" x-collapse class="mt-1 space-y-1 pl-11">
                            <li>
                                <a href="{{ route('planes') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('planes') ? 'text-orange-600 font-bold' : 'text-gray-500 hover:text-orange-500 hover:bg-gray-50' }}">
                                    • Planes de Estudio
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('tarifas') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('tarifas') ? 'text-orange-600 font-bold' : 'text-gray-500 hover:text-orange-500 hover:bg-gray-50' }}">
                                    • Tarifas Generales
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('categorias-modulos') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('categorias-modulos') ? 'text-orange-600 font-bold' : 'text-gray-500 hover:text-orange-500 hover:bg-gray-50' }}">
                                    • Cat. de Módulos
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('articulos') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('articulos') ? 'text-orange-600 font-bold' : 'text-gray-500 hover:text-orange-500 hover:bg-gray-50' }}">
                                    • Inventario / Insumos
                                </a>
                            </li>
                        </ul>
                    </li>

                    @if(Auth::user()->rol === 'administrador')

                    <li>
                        <a href="{{ route('usuarios') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all {{ request()->routeIs('usuarios') ? 'bg-orange-50 text-orange-600 font-bold border-l-4 border-orange-500' : 'text-gray-600 hover:bg-gray-50 hover:text-orange-500' }}">
                            <i class="fa-solid fa-users-gear w-5 text-center {{ request()->routeIs('usuarios') ? 'text-orange-500' : 'text-gray-400' }}"></i>
                            <span>Usuarios y Accesos</span>
                        </a>
                    </li>
                    @endif 

                {{-- @endif  --}}
                {{-- Fin Área Restringida --}}

                <li class="text-xs font-black text-gray-400 px-4 mt-8 mb-2 tracking-wider border-t pt-4">REPORTES</li>
                <li x-data="{ open: {{ request()->routeIs('reporte-arqueo') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-3 rounded-lg transition-all text-gray-600 hover:bg-gray-50 hover:text-orange-500 focus:outline-none">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-chart-pie w-5 text-center text-gray-400"></i>
                            <span class="font-medium">Reportes</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform duration-300" :class="{'rotate-180': open}"></i>
                    </button>
                    <ul x-show="open" x-collapse class="mt-1 space-y-1 pl-11">
                        <li>
                            <a href="{{ route('reporte-arqueo') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('reporte-arqueo') ? 'text-orange-600 font-bold' : 'text-gray-500 hover:text-orange-500 hover:bg-gray-50' }}">
                                <i class="fa-solid fa-file-invoice mr-2 text-xs opacity-50"></i> Arqueo Diario
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            
            <div class="h-20"></div>
        </div>
    </aside>

    <main class="p-4 sm:ml-64 mt-16 min-h-screen bg-gray-50">
        <div class="bg-white shadow-sm border border-gray-100 rounded-2xl p-6 min-h-[80vh]">
            @yield('content')
        </div>
    </main>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('toast', (event) => {
                const data = event[0]; 
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });

                Toast.fire({
                    icon: data.icon,
                    title: data.title
                });
            });
        });
    </script>

</body>
</html>