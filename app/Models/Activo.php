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
}
