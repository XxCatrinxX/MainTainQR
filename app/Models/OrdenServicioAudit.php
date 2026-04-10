<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenServicioAudit extends Model
{
    protected $table = 'orden_servicio_audits';

    protected $fillable = [
        'orden_servicio_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'tipo_cambio',
        'usuario_responsable',
        'notificado',
        'fecha_notificacion',
    ];

    protected $casts = [
        'notificado' => 'boolean',
        'fecha_notificacion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function ordenServicio(): BelongsTo
    {
        return $this->belongsTo(OrdenServicio::class, 'orden_servicio_id');
    }

    /**
     * Obtiene auditorías no notificadas
     */
    public static function pendientesDeNotificar()
    {
        return self::where('notificado', false)
                   ->orderBy('created_at', 'asc')
                   ->get();
    }

    /**
     * Marca como notificado
     */
    public function marcarNotificado()
    {
        $this->update([
            'notificado' => true,
            'fecha_notificacion' => now(),
        ]);
    }
}
