<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteActivo extends Model
{
    protected $table = 'reportes_activos';
    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'id_activo',
        'id_usuario',
        'estado_reporte',
        'comentario',
        'fecha',
        'estado',
    ];

    public function activo()
    {
        return $this->belongsTo(Activo::class, 'id_activo', 'id_activo');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
