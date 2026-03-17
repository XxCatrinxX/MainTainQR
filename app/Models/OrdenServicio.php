<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenServicio extends Model
{
   protected $table = 'orden_servicios';
    protected $primaryKey = 'id_orden'; // <--- MUY IMPORTANTE

protected $fillable = [
    'id_cliente', 
    'id_equipo',
    'id_usuario',
    'problema_reportado',
    'diagnostico',
    'actividad_a_realizar',
    'estado',
    'costo_materiales', 
    'costo_servicio',
    'costo_total',
    'fecha_recepcion'
];
    public function cliente()
{
return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
}

public function equipo()
{
    return $this->belongsTo(Equipo::class, 'id_equipo');
}

}

