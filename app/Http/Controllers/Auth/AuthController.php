<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Mostrar formulario
    public function showLogin()
    {
        return view('auth.login');
    }

    // Procesar login
    public function login(Request $request)
    {
        $request->validate([
            'correo' => ['required', 'email'],
            'contrasena' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (\Illuminate\Support\Facades\Auth::attempt([
            'correo' => $request->correo,
            'password' => $request->contrasena, // 👈 SIEMPRE debe llamarse password aquí
            'estado' => 1, // opcional: solo usuarios activos
        ], $remember)) {

            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()
            ->withErrors(['correo' => 'Credenciales inválidas.'])
            ->onlyInput('correo');
    }


    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
