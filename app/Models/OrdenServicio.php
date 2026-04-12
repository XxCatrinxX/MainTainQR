<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Cliente;

class OrdenServicio extends Model
{
    protected $table = 'orden_servicios';
    protected $primaryKey = 'id';

    protected $fillable = [
        'folio',
        'equipo_id',
        'user_id',
        'falla_reportada',
        'estado_fisico',
        'estado',
        'decision_cliente',
        'token_rastreo',
        'fecha_recepcion',
        'fecha_diagnostico',
        'fecha_aprobacion',
        'fecha_estimada_entrega',
        'fecha_reparacion',
        'fecha_listo',
        'fecha_entrega_real',
        'mano_obra',
        'solucion_propuesta',
        'es_reparable',
        'monto_compra_piezas'
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'equipo_id');
    }

    public function user() // Representa al técnico asignado
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evidencias()
    {
        return $this->hasMany(Evidencia::class, 'orden_servicio_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function detallesTecnicos()
    {
        return $this->hasOne(DetalleTecnico::class, 'orden_servicio_id');
    }

    public function solicitudesCompra()
    {
        return $this->hasMany(SolicitudCompra::class, 'orden_servicio_id');
    }

    public function repuestos()
    {
        return $this->belongsToMany(Inventario::class, 'orden_repuestos', 'orden_servicio_id', 'inventario_id')
                    ->withPivot('cantidad', 'precio_fijado')
                    ->withTimestamps();
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id');
    }
}

