<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $table = 'equipos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'cliente_id',
        'tipo',
        'marca',
        'modelo',
        'numero_serie',
        'qr_token',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}