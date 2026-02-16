<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encargado extends Model
{
    protected $table = 'encargados';
    protected $primaryKey = 'id_encargado';

    protected $fillable = [
        'nombre',
        'tipo',
        'id_usuario',
        'estado',
    ];
}
