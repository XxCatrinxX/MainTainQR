<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudCompra extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_compra';

    protected $fillable = [
        'orden_servicio_id',
        'user_id',
        'nombre_pieza',
        'descripcion',
        'cantidad',
        'estado',
    ];

    public function ordenServicio()
    {
        return $this->belongsTo(OrdenServicio::class, 'orden_servicio_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
