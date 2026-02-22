<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    protected $table = 'activos';
    protected $primaryKey = 'id_activo';

    protected $fillable = [
        'codigo',
        'serial',
        'nombre',
        'descripcion',
        'tipo',
        'marca',
        'estado',
        'condicion',
        'fecha_adquisicion',
        'valor_compra',
        'id_categoria_activo',
        'fecha_registro',
        'registrado_por',
        'aprobado_por',
        'observaciones',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaActivo::class, 'id_categoria_activo', 'id_categoria_activo');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por', 'id_usuario');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoActivo::class, 'id_activo', 'id_activo');
    }
}
