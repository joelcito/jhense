<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'autos';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'cliente_id',
        'marca_id',
        'placa',
        'modelo',
        'motor',
        'anio_fab',
        'vin',
        'estado',
        'deleted_at'
    ];

    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function marca(){
        return $this->belongsTo(Marca::class, 'marca_id');
    }
}
