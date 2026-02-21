<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoActivo extends Model
{
    protected $table = 'movimientos_activos';
    protected $primaryKey = 'id_movimiento';

    protected $fillable = [
        'id_activo',
        'realizado_por',
        'tipo',
        'observaciones',
        'fecha',
        'estado',
    ];

    public function getRouteKeyName()
    {
        return 'id_movimiento';
    }
}
