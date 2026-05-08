<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrupoCliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'grupo_clientes';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'grupo_id',
        'cliente_id',
        'estado',
        'deleted_at'
    ];

    public function grupo(){
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function servicios(){
        return $this->hasMany(ClienteServicio::class, 'grupo_cliente_id');
    }
}
