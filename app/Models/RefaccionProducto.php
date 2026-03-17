<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefaccionProducto extends Model
{
    protected $table = 'refaccion_productos';
    protected $primaryKey = 'id_refaccion';

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'marca',
        'modelo',
        'precio',
        'stock',
        'stock_minimo',
        'estado',
        'tipo_pieza',
        'observaciones'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];
}
