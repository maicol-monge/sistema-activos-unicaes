<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EncargadoController extends Controller
{
    public function index()
    {
        // $encargados = Encargado::orderBy('id_encargado', 'desc')->paginate(10);
        $encargados = User::where('rol', 'ENCARGADO')
            ->orderBy('id_usuario', 'desc')
            ->paginate(10);
        return view('encargados.index', compact('encargados'));
    }

    public function create()
    {
        return view('encargados.create');
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nombre' => ['required', 'string', 'max:100'],
    //         'tipo' => ['required', 'in:PERSONA,UNIDAD'],
    //         'id_usuario' => ['nullable', 'exists:users,id_usuario'],
    //     ], [
    //         'nombre.required' => 'El nombre es obligatorio.',
    //         'tipo.required' => 'El tipo es obligatorio.',
    //         'tipo.in' => 'El tipo debe ser PERSONA o UNIDAD.',
    //         'id_usuario.exists' => 'El usuario seleccionado no existe.',
    //     ]);

    //     Encargado::create([
    //         'nombre' => $request->nombre,
    //         'tipo' => $request->tipo,
    //         'id_usuario' => $request->id_usuario ?: null,
    //         'estado' => 1, // ✅ disponible para futuras asignaciones
    //     ]);

    //     return redirect()->route('encargados.index')->with('ok', 'Encargado registrado correctamente.');
    // }
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'unique:users,correo'],
            'contrasena' => ['required', 'min:6'],
            'tipo' => ['required', 'in:PERSONA,UNIDAD'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.unique' => 'El correo ya está registrado.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo debe ser PERSONA o UNIDAD.',
        ]);

        User::create([
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'contrasena' => Hash::make($request->contrasena),
            'rol' => 'ENCARGADO',
            'tipo' => $request->tipo,
            'estado' => 1,
        ]);

        return redirect()->route('encargados.index')
            ->with('ok', 'Encargado registrado correctamente.');
    }

    public function edit(User $encargado)
    {
        if ($encargado->rol !== 'ENCARGADO') {
            abort(404);
        }

        return view('encargados.edit', compact('encargado'));
    }

    // public function update(Request $request, Encargado $encargado)
    // {
    //     $request->validate([
    //         'nombre' => ['required', 'string', 'max:100'],
    //         'tipo' => ['required', 'in:PERSONA,UNIDAD'],
    //         'id_usuario' => ['nullable', 'exists:users,id_usuario'],
    //         'estado' => ['required', 'in:0,1'],
    //     ], [
    //         'nombre.required' => 'El nombre es obligatorio.',
    //         'tipo.in' => 'El tipo debe ser PERSONA o UNIDAD.',
    //     ]);

    //     $encargado->update([
    //         'nombre' => $request->nombre,
    //         'tipo' => $request->tipo,
    //         'id_usuario' => $request->id_usuario ?: null,
    //         'estado' => (int) $request->estado,
    //     ]);

    //     return redirect()->route('encargados.index')->with('ok', 'Encargado actualizado correctamente.');
    // }
    public function update(Request $request, User $encargado)
    {
        if ($encargado->rol !== 'ENCARGADO') {
            abort(404);
        }

        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'unique:users,correo,' . $encargado->id_usuario . ',id_usuario'],
            'tipo' => ['required', 'in:PERSONA,UNIDAD'],
            'estado' => ['required', 'in:0,1'],
            'contrasena' => ['nullable', 'min:6'],
        ]);

        $data = [
            'nombre' => $request->nombre,
            'correo' => $request->correo,
            'tipo' => $request->tipo,
            'estado' => (int) $request->estado,
        ];

        if ($request->filled('contrasena')) {
            $data['contrasena'] = Hash::make($request->contrasena);
        }

        $encargado->update($data);

        return redirect()->route('encargados.index')
            ->with('ok', 'Encargado actualizado correctamente.');
    }

    // public function destroy(User $encargado)
    // {
    //     // Por ahora eliminación directa (luego podemos restringir si tiene asignaciones)
    //     $encargado->delete();

    //     return redirect()->route('encargados.index')->with('ok', 'Encargado eliminado correctamente.');
    // }
    public function destroy(User $encargado)
    {
        if ($encargado->rol !== 'ENCARGADO') {
            abort(404);
        }

        $encargado->update([
            'estado' => 0
        ]);

        return redirect()->route('encargados.index')
            ->with('ok', 'Encargado desactivado correctamente.');
    }
}
