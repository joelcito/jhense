<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'sucursales';

    protected $fillable = [
        'usuario_creador_id',
        'usuario_modificador_id',
        'usuario_eliminador_id',
        'nombre',
        'direccion',
        'codigo_sucursal',
        'estado',
        'deleted_at'
    ];

    public function movimientos(){
        return $this->hasMany(Movimiento::class);
    }

    public function pagos(){
        return $this->hasMany(Pago::class, 'sucursal_id')->orderBy('id', 'asc');
    }

    public function clientes(){
        return $this->hasMany(Cliente::class, 'sucursal_id')->orderBy('id', 'asc');
    }
}
