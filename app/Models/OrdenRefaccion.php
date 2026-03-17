<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenRefaccion extends Model
{
    protected $table = 'orden_refaccions';
    protected $primaryKey = 'id_orden_refaccion';

    protected $fillable = [
        'id_orden',
        'descripcion_refaccion',
        'costo_estimado',
        'costo_real',
        'estado',
        'fecha_solicitud',
        'fecha_aprobacion',
        'fecha_entrega',
        'observaciones'
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'fecha_entrega' => 'datetime',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenServicio::class, 'id_orden', 'id_orden');
    }
}
