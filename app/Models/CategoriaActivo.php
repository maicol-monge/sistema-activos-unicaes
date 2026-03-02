<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaActivo extends Model
{
    use HasFactory;

    protected $table = 'categorias_activos';
    protected $primaryKey = 'id_categoria_activo';

    protected $fillable = [
        'nombre',
        'estado',
    ];
}
