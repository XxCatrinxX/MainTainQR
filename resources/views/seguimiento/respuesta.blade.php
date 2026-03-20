<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Respuesta Registrada' }} — MaintainQR</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f9fafb; font-family: 'Inter', -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; }
        .card { background: #fff; border-radius: 16px; padding: 3rem 2.5rem; max-width: 480px; width: 100%; text-align: center; box-shadow: 0 8px 30px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
        .icon { font-size: 4rem; margin-bottom: 1.5rem; }
        .icon.success { color: #059669; }
        .icon.danger { color: #dc2626; }
        .icon.info { color: #0369a1; }
        h1 { font-size: 1.5rem; font-weight: 800; color: #111827; letter-spacing: -0.02em; margin-bottom: 1rem; }
        p { color: #6b7280; line-height: 1.65; font-size: 0.95rem; }
        .brand { margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #f3f4f6; color: #9ca3af; font-size: 0.8rem; }
        .brand strong { color: #374151; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon {{ $color ?? 'info' }}">
            <i class="fas fa-{{ $icono ?? 'check-circle' }}"></i>
        </div>
        <h1>{{ $titulo ?? 'Respuesta Registrada' }}</h1>
        <p>{{ $mensaje ?? '' }}</p>

        @if(isset($orden))
        <p style="margin-top:1rem; font-size:0.82rem; background:#f8fafc; border-radius:8px; padding:0.75rem; color:#374151;">
            Orden <strong>{{ $orden->folio }}</strong><br>
            {{ $orden->equipo->marca ?? '' }} {{ $orden->equipo->modelo ?? '' }}
        </p>
        @endif

        <div class="brand">
            <strong>MaintainQR</strong> · Soporte Técnico Profesional
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</body>
</html>
