<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BajaActivo extends Model
{
    protected $table = 'bajas_activos';
    protected $primaryKey = 'id_baja';

    protected $fillable = [
        'id_activo',
        'motivo',
        'dado_por',
        'fecha',
        'estado',
    ];
}
