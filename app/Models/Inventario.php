<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario';

    protected $fillable = [
        'nombre_pieza',
        'sku',
        'calidad',
        'stock',
        'precio_venta',
    ];

    public function orden_servicios()
    {
        return $this->belongsToMany(OrdenServicio::class, 'orden_repuestos', 'inventario_id', 'orden_servicio_id')
                    ->withPivot('cantidad', 'precio_fijado')
                    ->withTimestamps();
    }
}
