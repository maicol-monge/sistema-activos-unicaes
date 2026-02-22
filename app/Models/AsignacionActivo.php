<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionActivo extends Model
{
    protected $table = 'asignaciones_activos';
    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'id_activo',
        'id_usuario',
        'asignado_por',
        'estado_asignacion',
        'fecha_asignacion',
        'fecha_respuesta',
        'estado',
    ];

    public function activo()
    {
        return $this->belongsTo(\App\Models\Activo::class, 'id_activo', 'id_activo');
    }

    public function encargadoUsuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario', 'id_usuario');
    }

    public function usuarioAsignador()
    {
        return $this->belongsTo(\App\Models\User::class, 'asignado_por', 'id_usuario');
    }


    public function getRouteKeyName()
    {
        return 'id_asignacion';
    }
}
