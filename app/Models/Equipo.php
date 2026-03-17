<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $table = 'equipos';
    protected $primaryKey = 'id_equipo';

    protected $fillable = [
        'id_cliente',
        'tipo_equipo',
        'marca',
        'modelo',
        'num_serie',
        'color',
        'observaciones',
    ];
    public function cliente()
{
    return $this->belongsTo(Cliente::class, 'id_cliente');
}
}