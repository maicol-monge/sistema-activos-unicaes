<?php

namespace App\Http\Controllers;

use App\Models\Encargado;
use App\Models\User;
use Illuminate\Http\Request;

class EncargadoController extends Controller
{
    public function index()
    {
        $encargados = Encargado::orderBy('id_encargado', 'desc')->paginate(10);
        return view('encargados.index', compact('encargados'));
    }

    public function create()
    {
        // Usuarios opcionales a asociar (si quieres solo activos, filtra estado=1)
        $usuarios = User::orderBy('nombre')->get();

        return view('encargados.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'in:PERSONA,UNIDAD'],
            'id_usuario' => ['nullable', 'exists:users,id_usuario'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'tipo.required' => 'El tipo es obligatorio.',
            'tipo.in' => 'El tipo debe ser PERSONA o UNIDAD.',
            'id_usuario.exists' => 'El usuario seleccionado no existe.',
        ]);

        Encargado::create([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'id_usuario' => $request->id_usuario ?: null,
            'estado' => 1, // ✅ disponible para futuras asignaciones
        ]);

        return redirect()->route('encargados.index')->with('ok', 'Encargado registrado correctamente.');
    }

    public function edit(Encargado $encargado)
    {
        $usuarios = User::orderBy('nombre')->get();
        return view('encargados.edit', compact('encargado', 'usuarios'));
    }

    public function update(Request $request, Encargado $encargado)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'in:PERSONA,UNIDAD'],
            'id_usuario' => ['nullable', 'exists:users,id_usuario'],
            'estado' => ['required', 'in:0,1'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'tipo.in' => 'El tipo debe ser PERSONA o UNIDAD.',
        ]);

        $encargado->update([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'id_usuario' => $request->id_usuario ?: null,
            'estado' => (int) $request->estado,
        ]);

        return redirect()->route('encargados.index')->with('ok', 'Encargado actualizado correctamente.');
    }

    public function destroy(Encargado $encargado)
    {
        // Por ahora eliminación directa (luego podemos restringir si tiene asignaciones)
        $encargado->delete();

        return redirect()->route('encargados.index')->with('ok', 'Encargado eliminado correctamente.');
    }
}
