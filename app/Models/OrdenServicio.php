<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenServicio extends Model
{
   protected $table = 'orden_servicios';
    protected $primaryKey = 'id_orden'; // <--- MUY IMPORTANTE

    protected $fillable = [
        'id_equipo', 'id_usuario', 'problema_reportado', 'diagnostico',
        'actividad_a_realizar', 'estado', 'costo_materiales', 
        'costo_servicio', 'costo_total', 'fecha_recepcion'
    ];
}
