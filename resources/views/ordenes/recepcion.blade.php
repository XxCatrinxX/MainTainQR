@extends('adminlte::page')

@section('title', 'Orden Recibida - QR')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card text-center" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <div class="card-body p-5">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem; opacity: 0.8;"></i>
                <h2 class="mt-4" style="font-weight: 700;">¡Orden Generada Exitosamente!</h2>
                <h4 class="text-muted mb-4">Folio: <strong>{{ $orden->folio }}</strong></h4>

                <div class="p-3 mb-4" style="background-color: #fafafa; border-radius: 8px; border: 1px dashed #ccc;">
                    <p class="mb-2 text-muted">Token de rastreo del cliente:</p>
                    <code style="font-size: 1.2rem; background: transparent; color: #111827;">{{ $orden->token_rastreo }}</code>
                    <br><br>
                    <a href="{{ route('seguimiento.show', $orden->token_rastreo) }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-2">
                        <i class="fas fa-external-link-alt"></i> Ver enlace de seguimiento
                    </a>
                </div>

                <hr>

                <h5 class="mb-3">Código QR para el Equipo</h5>
                <p class="text-muted small">Imprime este código y pégalo en el dispositivo del cliente para que el técnico lo escanee.</p>
                
                <div class="mb-4">
                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR Code Equipo" style="border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                </div>

                <a href="{{ route('home') }}" class="btn btn-dark" style="border-radius: 8px; font-weight: 500;">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
