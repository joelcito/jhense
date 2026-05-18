<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormularioAutorizacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'formulario_autorizaciones';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'orden_recepcion_id',
        'fecha',
        'cite',
        'observaciones',
        'preventivo',
        'correctivo',
        'repuestos_suministros',
        'otros',
        'total_general',
        'estado',
        'deleted_at'
    ];

    public function ordenRecepcion()
    {
        return $this->belongsTo(OrdenRecepcion::class, 'orden_recepcion_id');
    }
}
