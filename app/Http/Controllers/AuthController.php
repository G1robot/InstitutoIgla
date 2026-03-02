<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UsuarioModel;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'password' => 'required'
        ]);

        $credenciales = [
            'usuario' => $request->usuario,
            'password' => $request->password,
        ];

        // Auth::attempt() busca al usuario y compara la contraseña encriptada
        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();
            return redirect()->intended('/'); // Redirige al inicio
        }

        // Si falla, regresa con error
        return back()->withErrors([
            'login_error' => 'Las credenciales proporcionadas no son correctas.',
        ])->onlyInput('usuario');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
