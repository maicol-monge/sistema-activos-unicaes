<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'rol' => ['nullable', 'in:ADMIN,INVENTARIADOR,ENCARGADO,DECANO'],
            'tipo' => ['nullable', 'in:PERSONA,UNIDAD'],
            'estado' => ['nullable', 'in:0,1'],
        ]);

        $users = User::query()
            ->when(!empty($filtros['q']), function ($query) use ($filtros) {
                $texto = trim($filtros['q']);
                $query->where(function ($sub) use ($texto) {
                    $sub->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('correo', 'like', "%{$texto}%");
                });
            })
            ->when(!empty($filtros['rol']), fn($query) => $query->where('rol', $filtros['rol']))
            ->when(!empty($filtros['tipo']), fn($query) => $query->where('tipo', $filtros['tipo']))
            ->when(isset($filtros['estado']) && $filtros['estado'] !== '', fn($query) => $query->where('estado', (int) $filtros['estado']))
            ->orderBy('id_usuario', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'filtros'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150', 'unique:users,correo'],
            'contrasena' => ['required', 'string', 'min:8'],
            'rol' => ['required', 'in:ADMIN,INVENTARIADOR,ENCARGADO,DECANO'],
            'tipo' => ['required', 'in:PERSONA,UNIDAD'],
            'estado' => ['required', 'in:0,1'],
        ], [
            'correo.unique' => 'El correo electrónico ya está registrado.',
            'correo.required' => 'El correo es obligatorio.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'contrasena.required' => 'La contraseña es obligatoria.',
        ]);

        User::create([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'contrasena' => Hash::make($request->contrasena),
            'rol' => $request->rol,
            'tipo' => $request->tipo,
            'estado' => (int) $request->estado,
        ]);

        return redirect()->route('users.index')->with('ok', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150', 'unique:users,correo,' . $user->id_usuario . ',id_usuario'],
            'rol' => ['required', 'in:ADMIN,INVENTARIADOR,ENCARGADO,DECANO'],
            'tipo' => ['required', 'in:PERSONA,UNIDAD'],
            'estado' => ['required', 'in:0,1'],
            'contrasena' => ['nullable', 'string', 'min:8'],
        ], [
            'correo.unique' => 'Ese correo ya pertenece a otro usuario.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $user->nombre = $request->nombre;
        $user->correo = $request->correo;
        $user->rol = $request->rol;
        $user->tipo = $request->tipo;
        $user->estado = (int) $request->estado;

        if ($request->filled('contrasena')) {
            $user->contrasena = Hash::make($request->contrasena);
        }

        $user->save();

        return redirect()->route('users.index')->with('ok', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() == $user->id_usuario) {
            return back()->with('err', 'No puedes eliminar tu propio usuario.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('ok', 'Usuario eliminado correctamente.');
    }
}
