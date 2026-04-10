<!-- resources/views/seguimiento/monitor.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        📦 Monitoreo en Tiempo Real - Orden {{ $orden->folio }}
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Estado Actual -->
                    <div class="alert alert-info">
                        <strong>Estado Actual:</strong>
                        <span id="estado-actual" class="badge badge-primary">{{ $orden->estado }}</span>
                    </div>

                    <!-- Timeline de Cambios -->
                    <h6 class="mt-4 mb-3">📋 Historial de Cambios</h6>
                    <div id="timeline" class="list-group">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const tokenRastreo = '{{ $orden->token_rastreo }}';
const apiUrl = `/api/orders/${tokenRastreo}`;
let ultimoId = 0;

// Función para obtener cambios recientes
async function obtenerCambios() {
    try {
        const response = await fetch(`${apiUrl}/audits/recent`);
        const data = await response.json();

        console.log('Cambios recientes:', data);

        // Actualizar estado actual
        document.getElementById('estado-actual').textContent = data.estado_actual;

        // Actualizar timeline
        const timeline = document.getElementById('timeline');
        
        if (data.auditorias.length === 0) {
            timeline.innerHTML = '<p class="text-muted">Sin cambios en los últimos 5 minutos</p>';
        } else {
            timeline.innerHTML = data.auditorias.map(audit => `
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">
                            <strong>${audit.campo}</strong>
                            <span class="badge badge-secondary">${audit.tipo_cambio}</span>
                        </h6>
                        <small class="text-muted">${audit.hace}</small>
                    </div>
                    <p class="mb-1">
                        <code>${audit.valor_anterior || '—'}</code>
                        <span class="text-success">→</span>
                        <code>${audit.valor_nuevo}</code>
                    </p>
                    <small class="text-muted">Por: ${audit.usuario_responsable}</small>
                </div>
            `).join('');
        }

    } catch (error) {
        console.error('Error obteniendo cambios:', error);
    }
}

// Opción A: Polling cada 10 segundos
setInterval(obtenerCambios, 10000);

// Cargar inmediatamente
obtenerCambios();

// Opción B: Server-Sent Events (Recomendado para tiempo real)
function iniciarSSE() {
    const eventSource = new EventSource(`${apiUrl}/audits/stream`);

    eventSource.addEventListener('message', (event) => {
        console.log('Cambio en tiempo real:', event.data);
        obtenerCambios(); // Recargar timeline
        
        // Opcional: Reproducir sonido
        // new Audio('/sounds/notification.mp3').play();
    });

    eventSource.addEventListener('error', (event) => {
        console.error('Error SSE:', event);
        eventSource.close();
    });
}

// Descomenta para usar SSE en lugar de polling
// iniciarSSE();
</script>

<style>
#timeline {
    max-height: 500px;
    overflow-y: auto;
}

.list-group-item {
    border-left: 4px solid #0d6efd;
    padding: 1rem;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

code {
    background-color: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
}
</style>
@endsection
