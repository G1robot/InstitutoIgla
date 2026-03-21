<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>IGLA - Iniciar Sesión</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo1.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-900 flex justify-center items-center min-h-screen relative overflow-hidden">
    
    <div class="absolute inset-0 bg-cover bg-center blur-[6px] opacity-30 transform scale-105" 
         style="background-image: url('/img/fondo2.jpg');">
    </div>

    <div class="relative bg-gray-900 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.7)] overflow-hidden flex max-w-4xl w-full mx-4 border border-gray-800 min-h-[450px]">
        
        <div class="hidden md:block md:w-5/12 bg-cover bg-center relative" style="background-image: url('/img/fondo2.jpg')">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent to-gray-900"></div>
        </div>

        <div class="w-full md:w-7/12 flex justify-center items-center p-10 sm:p-14 relative z-10">
            <div class="w-full max-w-sm">
                
                <div class="flex flex-col items-center mb-8">
                    {{-- AQUÍ PON TU LOGO BLANCO EN PNG --}}
                    <img src="/img/LOGO BLANCO.png" alt="Logo IGLA" class="h-20 mb-3 object-contain drop-shadow-md">
                    <h2 class="text-center text-2xl font-bold text-white tracking-wide">Bienvenido</h2>
                    <p class="text-gray-400 text-sm mt-1">Ingresa tus credenciales para continuar</p>
                </div>

                @if($errors->any())
                    <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-3 rounded-lg mb-6 flex items-start gap-3 animate-fade-in-down" role="alert">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <div class="text-sm">
                            <p class="font-bold">Acceso denegado</p>
                            <p>{{ $errors->first('login_error') }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" autocomplete="off">
                    @csrf
                    
                    <div class="mb-5">
                        <label for="usuario" class="block text-gray-300 text-xs font-bold uppercase tracking-wider mb-2">Usuario</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-user text-gray-500"></i>
                            </div>
                            <input type="text" name="usuario" id="usuario" value="{{ old('usuario') }}" autocomplete="new-password"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-10 px-4 py-3 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-colors placeholder-gray-500" 
                                placeholder="Tu nombre de usuario" required>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label for="password" class="block text-gray-300 text-xs font-bold uppercase tracking-wider mb-2">Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-lock text-gray-500"></i>
                            </div>
                            <input type="password" name="password" id="password" autocomplete="new-password"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg pl-10 px-4 py-3 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-colors placeholder-gray-500" 
                                placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-red-600 text-white font-bold py-3 rounded-lg shadow-[0_4px_14px_0_rgba(220,38,38,0.39)] hover:bg-red-700 hover:shadow-[0_6px_20px_rgba(220,38,38,0.23)] transition-all active:scale-95 flex justify-center items-center gap-2">
                        <span>Ingresar al Sistema</span>
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    </button>
                </form>

            </div>
        </div>
    </div>
</body>
<div class="absolute bottom-4 text-center w-full text-gray-500 text-xs tracking-widest">
    &copy; {{ date('Y') }} IGLA ERP - Sistema de Gestión Institucional. Todos los derechos reservados.
</div>
</html>