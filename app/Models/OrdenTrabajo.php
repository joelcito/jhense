<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenTrabajo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orden_trabajos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'orden_recepcion_id',
        'numero_orden_secuencial',
        'anio',
        'fecha_emision',
        'mano_obra',
        'repuestos',
        'insumos',
        'trabajos_tercero',
        'subtotal_mano_obra',
        'subtotal_repuestos',
        'subtotal_insumos',
        'subtotal_trabajos_tercero',
        'total_general',
        'estado',
        'deleted_at'
    ];

    public function ordenRecepcion()
    {
        return $this->belongsTo(OrdenRecepcion::class, 'orden_recepcion_id');
    }
}
