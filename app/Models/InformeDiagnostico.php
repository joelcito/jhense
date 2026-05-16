<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InformeDiagnostico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'informe_diagnosticos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'orden_recepcion_id',
        'mecanico_id',
        'inspeccion_exterior_texto',
        'inspeccion_exterior_imagen',
        'inspeccion_interior_texto',
        'inspeccion_interior_imagen',
        'diagnosticos',
        'estado',
        'deleted_at'
    ];

    public function ordenRecepcion()
    {
        return $this->belongsTo(OrdenRecepcion::class, 'orden_recepcion_id');
    }

    public function mecanico()
    {
        return $this->belongsTo(User::class, 'mecanico_id');
    }
}
