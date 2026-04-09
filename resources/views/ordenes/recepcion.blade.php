@extends('adminlte::page')

@section('title', 'Orden Recibida - QR')

@section('css')
<style>
/* 📄 TICKET NORMAL (pantalla) */
#ticket {
    width: 260px;
    margin: auto;
    padding: 10px;
    font-family: monospace;
    font-size: 12px;
    text-align: center;
    border: 1px dashed #000;
}

/* Centrar QR */
#ticket img {
    display: block;
    margin: 10px auto;
    width: 140px;
}

/* 🖨️ MODO IMPRESIÓN */
@media print {

    body {
        margin: 0;
        padding: 0;
    }

    /* Ocultar todo */
    body * {
        visibility: hidden;
    }

    /* Mostrar solo el ticket */
    #ticket, #ticket * {
        visibility: visible;
    }

    /* Posicionar ticket */
    #ticket {
        position: absolute;
        left: 0;
        top: 0;
        width: 58mm; /* 🔥 cambia a 80mm si tu impresora es de 80 */
        padding: 5px;
        margin: 0;
        border: none;
    }

    /* Configuración de hoja */
    @page {
        size: 58mm auto; /* 🔥 80mm si aplica */
        margin: 0;
    }
}
</style>
@endsection

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-12 col-lg-8">
        <div class="card text-center" style="border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <div class="card-body p-5">

                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>

                <h2 class="mt-4">¡Orden Generada Exitosamente!</h2>

                <h4 class="text-muted mb-4">
                    Folio: <strong>{{ $orden->folio }}</strong>
                </h4>

                <!-- TOKEN -->
                <div class="p-3 mb-4" style="background-color: #fafafa; border-radius: 8px; border: 1px dashed #ccc;">
                    <p class="mb-2 text-muted">Token de rastreo:</p>

                    <code style="font-size: 1.2rem;">
                        {{ $orden->token_rastreo }}
                    </code>

                    <br><br>

                    <a href="{{ route('seguimiento.show', $orden->token_rastreo) }}"
                       target="_blank"
                       class="btn btn-sm btn-outline-secondary">
                        Ver seguimiento
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

                    <img src="data:image/svg+xml;base64,{{ $qrBase64 }}">

                    <p>Escanea para seguimiento</p>

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

                    <a href="{{ route('home') }}" class="btn btn-secondary">
                        Volver al inicio
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
    window.print();
}
</script>
@endsection