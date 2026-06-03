<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudRepuesto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitud_repuestos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'orden_recepcion_id',
        'sucursal_id',
        'tipo_solicitud',
        'total_general',
        'estado',
        'observaciones',
        'deleted_at'
    ];

    public function ordenRecepcion()
    {
        return $this->belongsTo(OrdenRecepcion::class, 'orden_recepcion_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function detalles()
    {
        return $this->hasMany(SolicitudRepuestoDetalle::class, 'solicitud_repuesto_id');
    }
}
