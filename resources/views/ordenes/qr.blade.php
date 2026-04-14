@extends('adminlte::page')

@section('title', 'QR de la Orden')

@section('css')
<style>
.card-qr {
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
}

.qr-container {
    text-align: center;
    padding: 15px;
}

.qr-container img {
    display: block;
    margin: 0 auto;
    width: 180px;
}

/* Info orden */
.info-item {
    margin-bottom: 10px;
}

.info-label {
    font-size: 0.75rem;
    color: #6b7280;
}

.info-value {
    font-weight: 600;
    color: #111827;
}

/* Impresión */
@media print {
    body * {
        visibility: hidden;
    }

    #print-area, #print-area * {
        visibility: visible;
    }

    #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>
@endsection

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-8">

        <div class="card card-qr p-4" id="print-area">

            <!-- HEADER -->
            <div class="text-center mb-3">
                <h3 style="font-weight:700;">Orden #{{ $orden->folio }}</h3>
                <span class="badge badge-secondary">{{ ucfirst($orden->estado) }}</span>
            </div>

            <hr>

            <!-- INFO -->
            <div class="row text-left">

                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Cliente</div>
                        <div class="info-value">
                            {{ $orden->cliente->nombre ?? 'N/A' }}
                            {{ $orden->cliente->apellido_paterno ?? '' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Teléfono</div>
                        <div class="info-value">
                            {{ $orden->cliente->telefono ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Equipo</div>
                        <div class="info-value">
                            {{ $orden->equipo->tipo }} - {{ $orden->equipo->marca }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Modelo</div>
                        <div class="info-value">
                            {{ $orden->equipo->modelo ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Fecha de recepción</div>
                        <div class="info-value">
                            {{ \Carbon\Carbon::parse($orden->fecha_recepcion)->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Técnico asignado</div>
                        <div class="info-value">
                            {{ $orden->user->name ?? 'No asignado' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Falla reportada</div>
                        <div class="info-value">
                            {{ $orden->falla_reportada }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Token de rastreo</div>
                        <div class="info-value">
                            {{ $orden->token_rastreo }}
                        </div>
                    </div>
                </div>

            </div>

            <hr>

            <!-- QR -->
            <div class="qr-container">
                <img src="data:image/svg+xml;base64,{{ $qrBase64 }}">
                <p class="text-muted mt-2" style="font-size: 0.85rem;">
                    Escanea para seguimiento
                </p>
            </div>

        </div>

        <!-- BOTONES -->
        <div class="text-center mt-3">
            <button onclick="window.print()" class="btn btn-dark mr-2">
                🖨️ Imprimir
            </button>

            <a href="{{ route('ordenes.index') }}" class="btn btn-secondary">
                ← Volver
            </a>
        </div>

    </div>
</div>
@endsection