<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EliminacionActivo extends Model
{
    protected $table = 'eliminaciones_activos';
    protected $primaryKey = 'id_eliminacion';

    protected $fillable = [
        'id_activo',
        'eliminado_por',
        'motivo',
        'fecha',
        'estado',
    ];
}
