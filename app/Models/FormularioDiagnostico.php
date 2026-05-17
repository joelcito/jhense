<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormularioDiagnostico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'formulario_diagnosticos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'orden_recepcion_id',
        'responsable_vehiculo',
        'vehiculo_asignado_a',
        'servicios_preventivos',
        'servicios_correctivos',
        'servicios_otros',
        'recepcion_taller',
        'estado',
        'deleted_at'
    ];

    public function ordenRecepcion()
    {
        return $this->belongsTo(OrdenRecepcion::class, 'orden_recepcion_id');
    }
}
