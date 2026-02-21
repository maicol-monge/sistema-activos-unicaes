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

        $user = \App\Models\User::where('correo', $request->correo)->first();

        if (!$user) {
            return back()
                ->withErrors(['correo' => 'El usuario no existe.'])
                ->onlyInput('correo');
        }

        if ($user->estado == 0) {
            return back()
                ->withErrors(['correo' => 'El usuario no está activo.'])
                ->onlyInput('correo');
        }

        if (\Illuminate\Support\Facades\Auth::attempt([
            'correo' => $request->correo,
            'password' => $request->contrasena,
        ])) {

            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()
            ->withErrors(['correo' => 'Contraseña incorrecta.'])
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
