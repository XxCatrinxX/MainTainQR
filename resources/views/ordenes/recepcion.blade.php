@extends('adminlte::page')

@section('title', 'Orden Recibida - QR')

@section('css')
<style>
#ticket {
    width: 100%;
    max-width: 300px;
    margin: auto;
    border: 1px dashed #000;
    padding: 10px;
    font-family: monospace;
    font-size: 12px;
    text-align: center;
}

#ticket img {
    display: block;
    margin: 15px auto;
    width: 140px;
}
</style>
@endsection

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-12 col-lg-10 offset-lg-1">
        <div class="card text-center" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <div class="card-body p-5 text-center">

                <i class="fas fa-check-circle text-success" style="font-size: 4rem; opacity: 0.8;"></i>

                <h2 class="mt-4" style="font-weight: 700;">¡Orden Generada Exitosamente!</h2>

                <h4 class="text-muted mb-4">
                    Folio: <strong>{{ $orden->folio }}</strong>
                </h4>

                <!-- TOKEN -->
                <div class="p-3 mb-4" style="background-color: #fafafa; border-radius: 8px; border: 1px dashed #ccc;">
                    <p class="mb-2 text-muted">Token de rastreo del cliente:</p>

                    <code style="font-size: 1.2rem; background: transparent; color: #111827;">
                        {{ $orden->token_rastreo }}
                    </code>

                    <br><br>

                    <a href="{{ route('seguimiento.show', $orden->token_rastreo) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-secondary mt-2">
                        <i class="fas fa-external-link-alt"></i> Ver enlace de seguimiento
                    </a>
                </div>

                <hr>

                <!-- 🎫 TICKET -->
                <div id="ticket">
                    <h4>Orden #{{ $orden->folio }}</h4>

                    <p>
                        <strong>Cliente:</strong><br>
                        {{ $orden->equipo->cliente->nombre }}
                        {{ $orden->equipo->cliente->apellido_paterno }}
                    </p>

                    <p>
                        <strong>Equipo:</strong><br>
                        {{ $orden->equipo->tipo }} - {{ $orden->equipo->marca }}
                    </p>

                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" width="150">

                    <p style="margin-top:10px;">Escanea para seguimiento</p>

                    <hr>

                    <p>Gracias por su preferencia</p>
                    <p style="font-size:10px;">Conserve este ticket</p>
                </div>

                <!-- BOTONES -->
                <div class="mt-3">
                    <button onclick="imprimirTicket()" class="btn btn-dark mb-2">
                        🖨️ Imprimir Ticket
                    </button>

                    <br>

                    <a href="{{ route('home') }}"
                       class="btn btn-secondary"
                       style="border-radius: 8px; font-weight: 500;">
                        <i class="fas fa-arrow-left mr-1"></i> Volver al Inicio
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
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
                    width: 250px;
                    margin: auto;
                }
                h4 {
                    margin: 5px 0;
                }
                p {
                    margin: 5px 0;
                }
                img {
                    margin-top: 10px;
                }
                hr {
                    border-top: 1px dashed #000;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            ${contenido}
        </body>
        </html>
    `);

    ventana.document.close();

    // Esperar a que cargue antes de imprimir
    ventana.onload = function() {
        ventana.focus();
        ventana.print();
        ventana.close();
    };
}
</script>
@endsection