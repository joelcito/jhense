<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActaDevolucionRepuesto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'acta_devolucion_repuestos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'orden_recepcion_id',
        'repuestos',
        'observaciones',
        'estado',
        'deleted_at',
    ];

    public function ordenRecepcion()
    {
        return $this->belongsTo(OrdenRecepcion::class, 'orden_recepcion_id');
    }

    public function usuarioCreador()
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }
}
