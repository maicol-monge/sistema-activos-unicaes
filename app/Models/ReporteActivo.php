<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteActivo extends Model
{
    protected $table = 'reportes_activos';
    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'id_activo',
        'id_encargado',
        'estado_reporte',
        'comentario',
        'fecha',
        'estado',
    ];
}
