<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Evidencia extends Model
{
    use HasFactory;

    protected $table = 'evidencias';

    protected $fillable = [
        'orden_servicio_id',
        'url_foto',
        'momento',
    ];

    /**
     * Accessor para obtener la URL completa de la imagen.
     */
    public function getUrlCompletaAttribute()
    {
        return url(Storage::url($this->url_foto));
    }

    public function orden_servicio()
    {
        return $this->belongsTo(OrdenServicio::class, 'orden_servicio_id');
    }
}
