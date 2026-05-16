<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consulta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'consultas';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'componente',
        'sintoma',
        'tipo_falla',
        'causa_probable',
        'estado_actual',
        'recomendacion',
        'riesgo_asociado',
        'justificacion',
        'estado',
        'deleted_at'
    ];
}
