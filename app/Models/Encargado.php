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

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    public function getRouteKeyName()
    {
        return 'id_encargado';
    }
}
