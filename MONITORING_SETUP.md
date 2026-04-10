# Sistema de Monitoreo de Órdenes en Tiempo Real

## 📋 Descripción

Sistema completo de detección automática de cambios en órdenes de servicio con notificaciones en tiempo real via Firebase Cloud Messaging (FCM).

### Características:
✅ **Auditoría automática** - Registra TODOS los cambios en órdenes  
✅ **Notificaciones en tiempo real** - Técnico recibe alerta al instante  
✅ **Sin intervención manual** - Sistema completamente automatizado  
✅ **APIs REST** - Endpoints públicos para consultar cambios  
✅ **Streaming SSE** - Monitoreo en vivo (opcional)  

---

## 🔧 Instalación

### 1. Ejecutar migraciones
```bash
php artisan migrate
```

Esta crea la tabla `orden_servicio_audits` que rastrea todos los cambios.

### 2. Iniciar el scheduler

El sistema depende del scheduler de Laravel. Debe ejecutarse continuamente:

**Opción A: Cron Job (Producción)**
```bash
* * * * * cd /path/to/MainTainQR && php artisan schedule:run >> /dev/null 2>&1
```

**Opción B: Manual (Desarrollo)**
```bash
php artisan schedule:work
```

Esto ejecutará `MonitorOrderChangesJob` cada 30 segundos.

### 3. Iniciar el queue worker (si usas jobs)

```bash
php artisan queue:work
```

> **Nota**: Si usas `QUEUE_CONNECTION=sync` en `.env`, los jobs se ejecutan inmediatamente.

---

## 🎯 Cómo funciona

### Flujo de notificación:

```
1. Técnico/Sistema actualiza orden
   ↓
2. Observer detecta cambio automáticamente
   ↓
3. Crea registro en tabla `orden_servicio_audits`
   ↓
4. Scheduler ejecuta `MonitorOrderChangesJob` cada 30s
   ↓
5. Job busca auditorías no notificadas
   ↓
6. Firebase envía notificación al técnico
   ↓
7. Marca como notificado
```

### Ejemplo: Cliente acepta presupuesto

```php
// En TrackingController::aceptarPresupuesto()
$orden->decision_cliente = 'acepta';
$orden->estado = 'reparacion';
$orden->save(); // ← Observer se ejecuta aquí
```

**Automáticamente:**
- Se crea registro en `orden_servicio_audits`
- Monitoreo detecta el cambio en 30 segundos máximo
- Se envía notificación FCM al técnico: "El cliente aceptó el presupuesto"

---

## 📡 APIs para el Cliente

### 1. Ver historial de cambios completo

```
GET /api/orders/{token_rastreo}/audits
```

**Respuesta:**
```json
{
  "orden_id": 123,
  "folio": "OS-2026-0001",
  "estado_actual": "reparacion",
  "cambios_totales": 5,
  "auditorias": [
    {
      "id": 1,
      "campo": "estado",
      "valor_anterior": "espera",
      "valor_nuevo": "reparacion",
      "tipo_cambio": "cliente",
      "usuario_responsable": "Sistema",
      "fecha": "2026-04-10T14:30:00Z",
      "hace": "2 minutes ago"
    },
    {
      "id": 2,
      "campo": "decision_cliente",
      "valor_anterior": "pendiente",
      "valor_nuevo": "acepta",
      "tipo_cambio": "cliente",
      "usuario_responsable": "Sistema",
      "fecha": "2026-04-10T14:30:00Z",
      "hace": "2 minutes ago"
    }
  ]
}
```

### 2. Ver solo cambios recientes (últimos 5 minutos)

```
GET /api/orders/{token_rastreo}/audits/recent
```

**Útil para:** Polling cada 10 segundos en el frontend

### 3. Stream en vivo (Server-Sent Events)

```
GET /api/orders/{token_rastreo}/audits/stream
```

**Conexión persistente que recibe cambios al instante:**

```javascript
// Ejemplo en frontend
const eventSource = new EventSource('/api/orders/ABC123/audits/stream');

eventSource.addEventListener('message', (event) => {
  const cambio = JSON.parse(event.data);
  console.log('Cambio detectado:', cambio);
  // Actualizar UI
});
```

---

## 🧪 Pruebas

### Probar manualmente el monitoreo

```bash
# Ejecutar una sola iteración del monitoreo
php artisan orders:monitor --immediate
```

**Output esperado:**
```
Ejecutando monitoreo de órdenes...
✓ Monitoreo completado exitosamente
```

### Ver logs en tiempo real

```bash
tail -f storage/logs/laravel.log | grep "MonitorOrderChanges"
```

### Simular un cambio de estado

```php
// En tinker
php artisan tinker

$orden = OrdenServicio::find(1);
$orden->estado = 'diagnostico';
$orden->save(); // Crea auditoría

// Ejecutar monitoreo
dispatch(new App\Jobs\MonitorOrderChangesJob());

// O verificar auditorías
App\Models\OrdenServicioAudit::where('orden_servicio_id', 1)->get();
```

---

## 📊 Base de datos

### Tabla `orden_servicio_audits`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | ID | PK |
| `orden_servicio_id` | FK | Orden modificada |
| `campo` | string | Nombre del campo ('estado', 'decision_cliente', etc) |
| `valor_anterior` | string | Valor antes del cambio |
| `valor_nuevo` | string | Valor después del cambio |
| `tipo_cambio` | enum | 'manual' / 'sistema' / 'cliente' |
| `usuario_responsable` | string | Quién hizo el cambio |
| `notificado` | boolean | ¿Se envió la notificación? |
| `fecha_notificacion` | datetime | Cuándo se notificó |
| `created_at` | datetime | Cuándo ocurrió el cambio |

---

## ⚙️ Configuración

### Intervalo de monitoreo

En `app/Console/Kernel.php`:

```php
// Cada 30 segundos (recomendado)
$schedule->job(MonitorOrderChangesJob::class)->everyThirtySeconds();

// Cada minuto
$schedule->job(MonitorOrderChangesJob::class)->everyMinute();

// Cada 5 minutos
$schedule->job(MonitorOrderChangesJob::class)->everyFiveMinutes();
```

### Campos rastreados

Ver `app/Observers/OrdenServicioObserver.php`:

```php
$camposTrackeados = [
    'estado',                    // Estado principal
    'decision_cliente',          // Aceptó/Rechazó presupuesto
    'fecha_reparacion',         // Cuándo comenzó reparación
    'fecha_entrega_real',       // Cuándo se entregó
    'mano_obra',                // Presupuesto
    'fecha_estimada_entrega',   // Fecha estimada
];
```

Para agregar/quitar campos, edita este array.

---

## 🐛 Troubleshooting

### No estoy recibiendo notificaciones

1. **Verifica el scheduler está corriendo:**
   ```bash
   # En producción, verifica cron
   grep CRON /var/log/syslog || journalctl -u cron
   
   # En desarrollo
   php artisan schedule:work
   ```

2. **Verifica los logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep "MonitorOrderChanges\|FCM"
   ```

3. **Verifica Firebase credentials:**
   ```bash
   # En .env, verifica FIREBASE_CREDENTIALS_PATH apunta a archivo válido
   test -f "$(grep FIREBASE_CREDENTIALS_PATH .env | cut -d= -f2 | tr -d '\r')" && echo "OK" || echo "NO EXISTE"
   ```

4. **Verifica token FCM del técnico:**
   ```php
   php artisan tinker
   User::find(1)->fcm_token // Debe tener valor
   ```

### Los cambios no aparecen en auditorías

1. **Verifica Observer está registrado:**
   ```php
   // En AppServiceProvider::boot()
   OrdenServicio::observe(OrdenServicioObserver::class);
   ```

2. **Verifica que los datos se estén modificando correctamente:**
   ```php
   php artisan tinker
   $orden = OrdenServicio::find(1);
   echo $orden->estado; // Ver valor exacto
   ```

3. **Verifica tabla de auditorías existe:**
   ```bash
   php artisan migrate:status
   ```

---

## 📝 Próximas mejoras

- [ ] Notificaciones al cliente también
- [ ] Dashboard de auditoría en panel admin
- [ ] Alertas por email
- [ ] Webhooks personalizados
- [ ] Rate limiting automático

---

## 📞 Soporte

Para más información o problemas, revisa los logs:
```bash
storage/logs/laravel.log
```
