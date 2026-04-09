@extends('adminlte::page')

@section('title', 'Orden Recibida - QR')

<style>
#ticket {
    width: 250px;
    margin: auto;
    border: 1px dashed #000;
    padding: 10px;
}
</style>

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

                <div id="ticket" style="text-align: center">
                    <h4>Orden #{{ $orden->id}}</h4>

                    <p><strong>Cliente:</strong> {{ $orden->equipo->cliente->nombre }} {{ $orden->equipo->cliente->nombre }}</p>
                    <p><strong>Equipo:</strong> {{ $orden->equipo->tipo }} - {{ $orden->equipo->marca }}</p>

                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="150">
                    
                    <p>Escanea para seguimiento</p>
                </div>

                <button onclick="imprimirTicket()" class="btn btn-dark">
                    🖨️ Imprimir Ticket
                </button>

                <a href="{{ route('home') }}" class="btn btn-dark" style="border-radius: 8px; font-weight: 500;">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function imprimirTicket() {
    let contenido = document.getElementById('ticket').innerHTML;

    let ventana = window.open('', '', 'width=300,height=600');

    ventana.document.write(`
        <html>
        <head>
            <title>Ticket</title>
            <style>
                body {
                    font-family: monospace;
                    text-align: center;
                    font-size: 12px;
                }
                img {
                    margin-top: 10px;
                }
            </style>
        </head>
        <body>
            ${contenido}
        </body>
        </html>
    `);

    ventana.document.close();
    ventana.print();
    ventana.close();
}
</script>
@endsection
