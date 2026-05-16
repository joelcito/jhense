<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenRecepcion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orden_recepciones';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'grupo_cliente_id',
        'auto_id',
        'fecha_recepcion',
        'tipo_unidad',
        'kilometraje',
        'objeto_contratacion',
        'porcentaje_combustible',
        'checklist',
        'observacion_general',
        'estado',
        'deleted_at'
    ];

    public function grupoCliente()
    {
        return $this->belongsTo(GrupoCliente::class, 'grupo_cliente_id');
    }

    public function auto()
    {
        return $this->belongsTo(Auto::class, 'auto_id');
    }
}
