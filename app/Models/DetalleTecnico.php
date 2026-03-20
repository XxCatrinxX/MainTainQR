<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleTecnico extends Model
{
    use HasFactory;

    protected $table = 'detalles_tecnicos';

    protected $fillable = [
        'orden_servicio_id',
        'solucion_propuesta',
        'trabajo_finalizado',
        'observaciones_internas',
    ];

    public function ordenServicio()
    {
        return $this->belongsTo(OrdenServicio::class, 'orden_servicio_id');
    }
}
