<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudRepuestoDetalle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitud_repuesto_detalles';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'solicitud_repuesto_id',
        'producto_id',
        'descripcion',
        'cantidad',
        'precio',
        'subtotal',
        'aprobado',
        'path_archivo',
        'estado',
        'deleted_at'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function solicitudRepuesto()
    {
        return $this->belongsTo(SolicitudRepuesto::class, 'solicitud_repuesto_id');
    }
}
