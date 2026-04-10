# 🚀 Guía Rápida: Sistema de Monitoreo de Órdenes

## El Problema
Tu compañero aceptó una orden pero no recibió notificación automática.

## La Solución
Sistema automático que:
1. ✅ Detecta cambios de estado de órdenes
2. ✅ Registra en auditoría quién hizo qué y cuándo
3. ✅ Envía notificación Firebase al técnico en <30 segundos
4. ✅ El cliente puede monitorear en tiempo real

---

## 📦 Archivos Creados

### Backend
```
app/
  ├── Models/
  │   └── OrdenServicioAudit.php          ← Modelo para auditoría
  ├── Observers/
  │   └── OrdenServicioObserver.php       ← Detecta cambios automáticamente
  ├── Jobs/
  │   └── MonitorOrderChangesJob.php      ← Envía notificaciones cada 30s
  ├── Console/
  │   ├── Kernel.php                      ← Configura scheduler
  │   └── Commands/
  │       └── MonitorOrderChanges.php     ← Comando artisan
  └── Http/Controllers/Api/
      └── OrderAuditController.php        ← APIs públicas de auditoría

database/migrations/
  └── 2026_04_10_000000_create_orden_servicio_audits_table.php

app/Providers/
  └── AppServiceProvider.php              ← Registra observer
```

### Frontend
```
resources/views/
  └── seguimiento/
      └── monitor.blade.php               ← Vista de monitoreo en tiempo real
```

### Documentación
```
MONITORING_SETUP.md                       ← Documentación completa
QUICK_SETUP.md                           ← Esta guía
```

---

## 🔧 Pasos de Implementación

### 1️⃣ Ejecutar migraciones
```bash
php artisan migrate
```

Esto crea la tabla `orden_servicio_audits` que almacena TODOS los cambios.

### 2️⃣ Iniciar el scheduler

**En desarrollo (sin cron):**
```bash
php artisan schedule:work
```

**En producción:** Agregar a crontab:
```
* * * * * cd /ruta/a/MainTainQR && php artisan schedule:run >> /dev/null 2>&1
```

### 3️⃣ Verificar que funciona

```bash
# Terminal 1: Ver logs en vivo
tail -f storage/logs/laravel.log | grep "MonitorOrderChanges"

# Terminal 2: Simular cambio (en tinker)
php artisan tinker
$orden = OrdenServicio::find(1);
$orden->estado = 'diagnostico';
$orden->save();

# Terminal 3: Ejecutar monitoreo manualmente
php artisan orders:monitor --immediate
```

**Output esperado:**
```
MonitorOrderChanges: Procesando 1 cambios
MonitorOrderChanges: Notificación enviada al técnico
FCM: Notificación enviada
```

---

## 🧪 Pruebas Rápidas

### Test 1: ¿Se crean auditorías?
```php
php artisan tinker

// Ve a la BD y cambia una orden
OrdenServicio::find(1)->update(['estado' => 'reparacion']);

// Verifica auditoría
OrdenServicioAudit::latest()->first();
```

### Test 2: ¿Se envían notificaciones?
```bash
# Ver últimas notificaciones en logs
tail -50 storage/logs/laravel.log | grep "FCM\|Notificación"
```

### Test 3: ¿Las APIs funcionan?
```bash
# Cambiar ABC123FAKE por un token_rastreo real
curl http://localhost:8000/api/orders/ABC123FAKE/audits/recent

# Respuesta esperada: JSON con cambios
```

---

## 📊 Flujo de Datos

```
Cliente/Técnico actualiza orden
    ↓
Observer captura cambio → crea OrdenServicioAudit
    ↓
Scheduler ejecuta MonitorOrderChangesJob cada 30s
    ↓
Job busca auditorías con notificado=false
    ↓
Firebase envía notificación FCM al técnico
    ↓
Marca como notificado=true
```

### Timeline esperado:
- **T+0s**: Cliente acepta presupuesto
- **T+0-1s**: Auditoría creada automáticamente
- **T+0-30s**: Scheduler detecta y envía notificación
- **T+30s**: ¡Técnico recibe notificación! 🔔

---

## 🎯 Campos Monitoreados

Automáticamente se rastrea:
- ✅ `estado` (recibido → diagnostico → espera → reparacion → listo → entregado)
- ✅ `decision_cliente` (pendiente → acepta → rechaza)
- ✅ `fecha_entrega_real` (Cuándo se entregó)
- ✅ `fecha_reparacion` (Cuándo comenzó)
- ✅ `mano_obra` (Cambios de presupuesto)
- ✅ `fecha_estimada_entrega` (Cambios de fecha estimada)

Para agregar más campos, edita `app/Observers/OrdenServicioObserver.php`.

---

## 🔌 APIs REST para el Cliente

### Ver historial completo
```
GET /api/orders/{token_rastreo}/audits
```

### Ver cambios últimos 5 minutos
```
GET /api/orders/{token_rastreo}/audits/recent
```

### Stream en vivo (SSE)
```
GET /api/orders/{token_rastreo}/audits/stream
```

---

## 🐛 Si no funciona

### Paso 1: Verifica que el scheduler esté corriendo
```bash
ps aux | grep "schedule:work"
```

Si no sale nada, inicia:
```bash
php artisan schedule:work &
```

### Paso 2: Verifica los logs
```bash
tail -100 storage/logs/laravel.log
```

Busca errores con "MonitorOrderChanges" o "FCM"

### Paso 3: Simula un cambio manualmente
```php
php artisan tinker
$orden = OrdenServicio::find(1);
$orden->estado = 'diagnostico';
$orden->save();

// Verifica auditoría
dd(OrdenServicioAudit::where('orden_servicio_id', 1)->latest()->first());

// Ejecuta monitoreo manualmente
app('App\Jobs\MonitorOrderChangesJob')->handle(app('App\Services\FirebaseNotificationService'));
```

### Paso 4: Verifica token FCM
```php
php artisan tinker
User::find(1)->fcm_token // Debe tener un valor válido
```

---

## 📝 Configuración Personalizada

### Cambiar intervalo de monitoreo

En `app/Console/Kernel.php`:

```php
// Más frecuente (más notificaciones)
->everyTenSeconds()      // Cada 10 segundos
->everyThirtySeconds()   // Cada 30 segundos (RECOMENDADO)
->everyMinute()          // Cada minuto
->everyFiveMinutes()     // Cada 5 minutos (menos recursos)
```

### Agregar nuevos campos a monitorear

En `app/Observers/OrdenServicioObserver.php`:

```php
$camposTrackeados = [
    'estado',
    'decision_cliente',
    'fecha_reparacion',
    'fecha_entrega_real',
    'mano_obra',
    'fecha_estimada_entrega',
    'tu_nuevo_campo',  // ← Agrega aquí
];
```

---

## ✅ Checklist de Instalación

- [ ] Ejecuté `php artisan migrate`
- [ ] Inicié el scheduler (`php artisan schedule:work` o cron)
- [ ] Los logs muestran "Schedule running" cada minuto
- [ ] Hice un cambio en una orden y se creó auditoría
- [ ] El técnico recibió notificación Firebase
- [ ] Las APIs REST responden correctamente

---

## 🎓 Conceptos Clave

| Término | Qué hace |
|---------|----------|
| **Observer** | Detecta cambios en modelos automáticamente |
| **Scheduler** | Ejecuta Jobs periódicamente |
| **Audit Trail** | Registro histór.ico de quién hizo qué y cuándo |
| **FCM (Firebase)** | Envía notificaciones push al móvil/app |
| **SSE** | Server-Sent Events para streaming en vivo |

---

## 📞 Próximos Pasos

1. ✅ Implementar este sistema
2. ⏭️ Agregar notificaciones al cliente también
3. ⏭️ Dashboard de auditoría en panel admin
4. ⏭️ Alertas por email
5. ⏭️ Webhooks para integraciones externas

---

**¿Preguntas?** Revisa `MONITORING_SETUP.md` para documentación completa.
