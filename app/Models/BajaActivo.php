<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BajaActivo extends Model
{
    protected $table = 'bajas_activos';
    protected $primaryKey = 'id_baja';

    protected $fillable = [
        'id_activo',
        'id_usuario_solicitante',
        'motivo',
        'estado',
    ];

    public function activo()
    {
        return $this->belongsTo(\App\Models\Activo::class, 'id_activo', 'id_activo');
    }

    public function solicitante()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_usuario_solicitante', 'id_usuario');
    }
}
