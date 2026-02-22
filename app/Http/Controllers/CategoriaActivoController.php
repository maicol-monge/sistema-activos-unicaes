<?php

namespace App\Http\Controllers;

use App\Models\CategoriaActivo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaActivoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'estado' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $categorias = CategoriaActivo::query()
            ->when(!empty($filtros['q']), function ($query) use ($filtros) {
                $texto = trim($filtros['q']);
                $query->where('nombre', 'like', "%{$texto}%");
            })
            ->when(isset($filtros['estado']) && $filtros['estado'] !== '', fn($query) => $query->where('estado', (int) $filtros['estado']))
            ->orderBy('id_categoria_activo', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('categorias.index', compact('categorias', 'filtros'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:categorias_activos,nombre'],
            'estado' => ['required', 'in:0,1'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        CategoriaActivo::create($data);

        return redirect()->route('categorias-activos.index')->with('ok', 'Categoría creada correctamente.');
    }

    public function show(string $id)
    {
        abort(404);
    }

    public function edit(CategoriaActivo $categorias_activo)
    {
        return view('categorias.edit', ['categoria' => $categorias_activo]);
    }

    public function update(Request $request, CategoriaActivo $categorias_activo)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50', 'unique:categorias_activos,nombre,' . $categorias_activo->id_categoria_activo . ',id_categoria_activo'],
            'estado' => ['required', 'in:0,1'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        $categorias_activo->update($data);

        return redirect()->route('categorias-activos.index')->with('ok', 'Categoría actualizada correctamente.');
    }

    public function destroy(CategoriaActivo $categorias_activo)
    {
        $categorias_activo->delete();

        return redirect()->route('categorias-activos.index')->with('ok', 'Categoría eliminada correctamente.');
    }
}
