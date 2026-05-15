<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteServicio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cliente_servicios';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'grupo_cliente_id',
        'item',
        'categoria',
        'sub_categoria',
        'nombre',
        'costo',
        'unidad_medida',
        'cantidad',
        'total',
        'estado',
        'deleted_at'
    ];
}
