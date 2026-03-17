<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    public $timestamps = false;

protected $fillable = [
    'nombre',
    'apellido_paterno',
    'apellido_materno',
    'telefono',
    'correo',
    'direccion',
    'fecha_registro',
];

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_cliente');
    }
}
