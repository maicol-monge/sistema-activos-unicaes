<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionActivo extends Model
{
    protected $table = 'asignaciones_activos';
    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'id_activo',
        'id_encargado',
        'asignado_por',
        'estado_asignacion',
        'fecha_asignacion',
        'fecha_respuesta',
        'estado',
    ];
}
