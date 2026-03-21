@extends('adminlte::page')

@section('title', 'Detalle de Orden — ' . $orden->folio)

@section('css')
{{-- Estilos centralizados en app.css --}}
@stop
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Orden <span style="font-family: monospace;">{{ $orden->folio }}</span></h1>
            <a href="{{ route('ordenes.index') }}" class="text-muted" style="font-size: 0.85rem; text-decoration: none;">
                <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
            </a>
        </div>
        <div>
            @php
                $colores  = ['recibido'=>'secondary','diagnostico'=>'warning','espera'=>'info','aceptado'=>'success','rechazado'=>'danger','reparacion'=>'primary','listo'=>'success','entregado'=>'dark'];
                $etiquetas = ['recibido'=>'Recibido','diagnostico'=>'Diagnóstico','espera'=>'Esperando Aprobación','aceptado'=>'Aprobado por Cliente','rechazado'=>'Rechazado por Cliente','reparacion'=>'En Reparación','listo'=>'Listo','entregado'=>'Entregado'];
                $c = $colores[$orden->estado] ?? 'secondary';
                $e = $etiquetas[$orden->estado] ?? $orden->estado;
            @endphp
            <span class="badge badge-{{ $c }}" style="font-size: 0.9rem; padding: 0.5em 1em;">{{ $e }}</span>
        </div>
    </div>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="border-radius: 8px; font-weight: 500; border: none; background: #dcfce7; color: #166534;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="border-radius: 8px; font-weight: 500;">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
@endif

{{-- TIMELINE --}}
<div class="card">
    <div class="card-body py-2 px-4">
        <div class="status-timeline">
            @foreach(['recibido'=>'Recibido','diagnostico'=>'Diagnóstico','espera'=>'En Espera','aceptado'=>'Aprobado','rechazado'=>'Rechazado','reparacion'=>'Reparación','listo'=>'Listo','entregado'=>'Entregado'] as $s => $label)
                @php
                    $estados   = ['recibido','diagnostico','espera','aceptado','rechazado','reparacion','listo','entregado'];
                    $posActual = array_search($orden->estado, $estados);
                    $posEste   = array_search($s, $estados);
                    $clase = $s === $orden->estado ? 'active' : ($posEste < $posActual ? 'done' : '');
                    if ($s === 'rechazado' && $orden->estado === 'aceptado') $clase = '';
                    if ($s === 'aceptado'  && $orden->estado === 'rechazado') $clase = '';
                @endphp
                <div class="st-step {{ $clase }}">{{ $label }}</div>
            @endforeach
        </div>
    </div>
</div>

@if($orden->estado === 'espera')
<div class="alert" style="border-radius:10px; background:#fffbeb; border:1px solid #fde68a; color:#92400e; font-weight:500; padding: 1rem 1.25rem;">
    <i class="fas fa-hourglass-half mr-2"></i>
    <strong>Esperando decisión del cliente.</strong> Se envió un correo con el presupuesto y los botones para Aceptar o Rechazar la reparación.
</div>
@endif

@if($orden->estado === 'rechazado')
<div class="alert" style="border-radius:10px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; font-weight:500; padding: 1rem 1.25rem;">
    <i class="fas fa-times-circle mr-2"></i>
    <strong>El cliente rechazó la reparación.</strong> Por favor coordina la devolución del equipo y cierra la orden.
</div>
@endif

@if($orden->estado === 'aceptado')
<div class="alert" style="border-radius:10px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; font-weight:500; padding: 1rem 1.25rem;">
    <i class="fas fa-check-circle mr-2"></i>
    <strong>¡El cliente aceptó la reparación!</strong> Puedes avanzar el estado a "En Reparación" para comenzar el trabajo.
</div>
@endif

<div class="row">
    {{-- COLUMNA IZQUIERDA --}}
    <div class="col-md-8">

        {{-- CAMBIAR ESTADO --}}
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-exchange-alt mr-2 text-primary"></i>Actualizar Estado</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('ordenes.update', $orden->id) }}" class="d-flex align-items-end gap-2">
                    @csrf @method('PUT')
                    <div class="form-group mb-0 mr-3" style="flex: 1;">
                        <label>Nuevo Estado</label>
                        <select name="estado" class="custom-select">
                            @foreach(['recibido'=>'Recibido','diagnostico'=>'Diagnóstico','espera'=>'En Espera','aceptado'=>'Aprobado por Cliente','rechazado'=>'Rechazado por Cliente','reparacion'=>'En Reparación','listo'=>'Listo para Entrega','entregado'=>'Entregado'] as $v => $l)
                                <option value="{{ $v }}" {{ $orden->estado === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary-modern mb-0"><i class="fas fa-check mr-1"></i>Aplicar</button>
                </form>
            </div>
        </div>

        {{-- DIAGNÓSTICO --}}
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-stethoscope mr-2 text-warning"></i>Diagnóstico Técnico</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('ordenes.diagnostico', $orden->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Solución Propuesta *</label>
                        <textarea name="solucion_propuesta" class="form-control" rows="3"
                            placeholder="Describe el diagnóstico y la solución propuesta...">{{ old('solucion_propuesta', $orden->solucion_propuesta) }}</textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label>Costo de Mano de Obra *</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text bg-white" style="border: 1px solid #d1d5db; border-right: none; border-radius: 8px 0 0 8px;">$</span></div>
                            <input type="number" name="mano_obra" class="form-control" step="0.01" min="0"
                                value="{{ old('mano_obra', $orden->mano_obra ?? '') }}"
                                style="border-left: none; border-radius: 0 8px 8px 0 !important;">
                        </div>
                    </div>
                    <div class="form-group mb-4">
                        <label>Refacciones Necesarias</label>
                        <div class="row mb-2">
                            <div class="col-md-7">
                                <select id="select-repuesto" class="custom-select select2">
                                    <option value="">-- Seleccionar pieza del inventario --</option>
                                    @foreach($inventario as $pieza)
                                        <option value="{{ $pieza->id }}" data-precio="{{ $pieza->precio_venta }}" data-nombre="{{ $pieza->nombre_pieza }}" data-stock="{{ $pieza->stock }}">
                                            {{ $pieza->nombre_pieza }} (Stock: {{ $pieza->stock }}) - ${{ number_format($pieza->precio_venta, 2) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" id="cant-repuesto" class="form-control" value="1" min="1" placeholder="Cant.">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btn-add-repuesto" class="btn btn-secondary btn-block">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="tabla-repuestos-diagnosis">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Pieza</th>
                                        <th width="100">Cant.</th>
                                        <th width="120">Precio</th>
                                        <th width="120">Subtotal</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Se llena con JS --}}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">Total Piezas:</th>
                                        <th id="total-piezas-diag">$0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <p class="text-xs text-muted mt-1">Si la pieza no está en inventario, usa el formulario de <strong>Solicitud de Compra</strong> abajo.</p>
                    </div>

                    <div class="form-group mb-4">
                        <label>Evidencia Fotográfica (puede seleccionar múltiples)</label>
                        <input type="file" name="fotos[]" class="form-control" multiple accept="image/*"
                            style="padding: 0.4rem; cursor: pointer;">
                        <small class="text-muted">Formatos: JPG, PNG, GIF. Máx 5 MB c/u.</small>
                    </div>

                    <div id="hidden-inputs-repuestos"></div>

                    <button type="submit" class="btn btn-primary-modern btn-block py-2">
                        <i class="fas fa-paper-plane mr-2"></i> Guardar Diagnóstico y Notificar al Cliente
                    </button>
                </form>

                <hr class="my-4">

                {{-- FORMULARIO SOLICITUD DE COMPRA --}}
                <div class="bg-light p-3 rounded" style="border: 1px dashed #ced4da;">
                    <h6 class="mb-3" style="font-weight: 700; color: #4b5563;"><i class="fas fa-shopping-cart mr-2"></i>¿Falta una pieza? Solicitar Compra</h6>
                    <form action="{{ route('solicitudes.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="orden_servicio_id" value="{{ $orden->id }}">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" name="nombre_pieza" class="form-control form-control-sm mb-2" placeholder="Nombre de la pieza..." required>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="cantidad" class="form-control form-control-sm mb-2" value="1" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-secondary btn-sm btn-block">Solicitar</button>
                            </div>
                            <div class="col-12">
                                <textarea name="descripcion" class="form-control form-control-sm" rows="1" placeholder="Detalles extra (opcional)..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- GALERÍA DE EVIDENCIAS --}}
        @if($orden->evidencias->count() > 0)
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-images mr-2 text-info"></i>Evidencias ({{ $orden->evidencias->count() }})</h5></div>
            <div class="card-body">
                <div class="evidence-gallery">
                    @foreach($orden->evidencias as $ev)
                        <div class="evidence-thumb">
                            <a href="{{ asset('storage/' . $ev->url_foto) }}" target="_blank">
                                <img src="{{ asset('storage/' . $ev->url_foto) }}" alt="Evidencia">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- FORMULARIO DE REPARACIÓN (Solo si está aceptado o en reparación) --}}
        @if(in_array($orden->estado, ['aceptado', 'reparacion']))
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0"><i class="fas fa-tools mr-2"></i>Finalizar Reparación</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('ordenes.detalle', $orden->id) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Trabajo Realizado *</label>
                        <textarea name="trabajo_finalizado" class="form-control" rows="3" required
                            placeholder="Describe qué se le hizo al equipo...">{{ old('trabajo_finalizado', $orden->detallesTecnicos->trabajo_finalizado ?? '') }}</textarea>
                    </div>
                    <div class="form-group mb-4">
                        <label>Observaciones Internas (no visibles para el cliente)</label>
                        <textarea name="observaciones_internas" class="form-control" rows="2"
                            placeholder="Notas para el equipo técnico...">{{ old('observaciones_internas', $orden->detallesTecnicos->observaciones_internas ?? '') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block py-2" onclick="return confirm('¿Seguro que deseas finalizar el trabajo? El equipo pasará a estado LISTO y se notificará al cliente.')">
                        <i class="fas fa-check-circle mr-2"></i> Registrar Trabajo y Marcar como LISTO
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- REPUESTOS USADOS --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-tools mr-2 text-secondary"></i>Repuestos / Materiales</h5>
            </div>
            @if($orden->repuestos->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>SKU / Pieza</th><th>Cantidad</th><th>Precio Unit.</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @php $totalRepuestos = 0; @endphp
                        @foreach($orden->repuestos as $r)
                        @php $sub = $r->pivot->cantidad * $r->pivot->precio_fijado; $totalRepuestos += $sub; @endphp
                        <tr>
                            <td><span style="font-weight:600;">{{ $r->nombre }}</span><br><small class="text-muted">{{ $r->sku }}</small></td>
                            <td>{{ $r->pivot->cantidad }}</td>
                            <td>${{ number_format($r->pivot->precio_fijado, 2) }}</td>
                            <td style="font-weight:700;">${{ number_format($sub, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr style="background: #f9fafb;">
                            <td colspan="3" class="text-right" style="font-weight:700; color:#374151;">Total Repuestos:</td>
                            <td style="font-weight:800; color:#111827;">${{ number_format($totalRepuestos, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-muted">No se han agregado repuestos aún.</div>
            @endif
        </div>

        {{-- HISTORIAL DE PAGOS --}}
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-receipt mr-2 text-success"></i>Historial de Cobros</h5></div>
            @if($orden->pagos->count() > 0)
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Monto</th><th>Método</th><th>Tipo</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @foreach($orden->pagos as $p)
                        <tr>
                            <td style="font-weight:700;">${{ number_format($p->monto, 2) }}</td>
                            <td class="text-capitalize">{{ $p->metodo_pago }}</td>
                            <td><span class="badge {{ $p->tipo_pago == 'anticipo' ? 'badge-info' : 'badge-success' }}">{{ ucfirst($p->tipo_pago) }}</span></td>
                            <td class="text-muted" style="font-size:0.82rem;">{{ \Carbon\Carbon::parse($p->created_at)->format('d M, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-muted">Aún no se registran pagos para esta orden.</div>
            @endif
        </div>

    </div>

    {{-- COLUMNA DERECHA --}}
    <div class="col-md-4">

        {{-- INFO CLIENTE/EQUIPO --}}
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-user mr-2 text-primary"></i>Datos del Cliente</h5></div>
            <div class="card-body">
                <div class="info-row"><span class="info-label">Nombre</span><span class="info-value">{{ $orden->equipo->cliente->nombre ?? '' }} {{ $orden->equipo->cliente->apellido_paterno ?? '' }}</span></div>
                <div class="info-row"><span class="info-label">Teléfono</span><span class="info-value">{{ $orden->equipo->cliente->telefono ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Correo</span><span class="info-value">{{ $orden->equipo->cliente->correo ?? 'N/A' }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-desktop mr-2 text-secondary"></i>Datos del Equipo</h5></div>
            <div class="card-body">
                <div class="info-row"><span class="info-label">Tipo</span><span class="info-value text-capitalize">{{ $orden->equipo->tipo ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Marca / Modelo</span><span class="info-value">{{ $orden->equipo->marca ?? '' }} {{ $orden->equipo->modelo ?? '' }}</span></div>
                <div class="info-row"><span class="info-label">N° Serie</span><span class="info-value" style="font-family:monospace; font-size:0.85rem;">{{ $orden->equipo->numero_serie ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="info-label">Estado Físico</span><span class="info-value">{{ $orden->estado_fisico }}</span></div>
                <div class="info-row"><span class="info-label">Falla Reportada</span><span class="info-value">{{ $orden->falla_reportada }}</span></div>
            </div>
        </div>

        {{-- RESUMEN FINANCIERO --}}
        <div class="card" style="border: 2px solid #e5e7eb !important;">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-calculator mr-2"></i>Resumen Financiero</h5></div>
            <div class="card-body">
                @php
                    $manoObra = $orden->mano_obra ?? 0;
                    $totalPiezas = $orden->repuestos->sum(fn($r) => $r->pivot->cantidad * $r->pivot->precio_fijado);
                    $totalDebe = $manoObra + $totalPiezas;
                    $totalPagado = $orden->pagos->sum('monto');
                    $saldo = $totalDebe - $totalPagado;
                @endphp
                <div class="info-row"><span class="info-label">Mano de Obra</span><span class="info-value">${{ number_format($manoObra, 2) }}</span></div>
                <div class="info-row"><span class="info-label">Repuestos</span><span class="info-value">${{ number_format($totalPiezas, 2) }}</span></div>
                <div class="info-row" style="border-top: 1px solid #f3f4f6; padding-top: 0.5rem; margin-top: 0.5rem;">
                    <span class="info-label">Total a Cobrar</span>
                    <span class="info-value" style="font-size: 1.2rem;">${{ number_format($totalDebe, 2) }}</span>
                </div>
                <div class="info-row"><span class="info-label">Total Cobrado</span><span class="info-value text-success">${{ number_format($totalPagado, 2) }}</span></div>
                <div class="info-row">
                    <span class="info-label">Saldo</span>
                    <span class="info-value {{ $saldo > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 1.1rem; font-weight: 800;">
                        {{ $saldo > 0 ? '-' : '' }}${{ number_format(abs($saldo), 2) }}
                        @if($saldo <= 0) <i class="fas fa-check-circle ml-1"></i> @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- COBRO RÁPIDO --}}
        <div class="card">
            <div class="card-header"><h5 class="card-title"><i class="fas fa-dollar-sign mr-2 text-success"></i>Registrar Cobro</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('ordenes.pago', $orden->id) }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Monto Recibido</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text bg-white" style="border: 1px solid #d1d5db; border-right: none; border-radius: 8px 0 0 8px;">$</span></div>
                            <input type="number" name="monto" class="form-control" step="0.01" min="1"
                                style="border-left: none; border-radius: 0 8px 8px 0 !important;"
                                placeholder="0.00">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label>Método de Pago</label>
                        <select name="metodo_pago" class="custom-select">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta (POS)</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>
                    <div class="form-group mb-4">
                        <label>Tipo</label>
                        <select name="tipo_pago" class="custom-select">
                            <option value="anticipo">Anticipo</option>
                            <option value="liquidacion">Liquidación Final</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary-modern btn-block">
                        <i class="fas fa-check-circle mr-1"></i> Asentar Cobro
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Inicializar Select2 si está disponible
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

        let repuestosSeleccionados = [];

        $('#btn-add-repuesto').click(function() {
            let select = $('#select-repuesto');
            let selectedOption = select.find('option:selected');
            let id = select.val();
            if (!id) return;
            
            let nombre = selectedOption.data('nombre');
            let precio = parseFloat(selectedOption.data('precio'));
            let stock = parseInt(selectedOption.data('stock'));
            let cant = parseInt($('#cant-repuesto').val());

            if (cant < 1) return;

            if (cant > stock) {
                alert('La cantidad excede el stock disponible (' + stock + ').');
                return;
            }

            // Verificar si ya existe en la lista
            let index = repuestosSeleccionados.findIndex(r => r.id === id);
            if (index !== -1) {
                if (repuestosSeleccionados[index].cant + cant > stock) {
                    alert('La cantidad total acumulada excede el stock disponible.');
                    return;
                }
                repuestosSeleccionados[index].cant += cant;
            } else {
                repuestosSeleccionados.push({ id, nombre, cant, precio });
            }

            renderTablaDiagnosis();
            select.val('').trigger('change');
            $('#cant-repuesto').val(1);
        });

        $(document).on('click', '.btn-remove-diag', function() {
            let index = $(this).data('index');
            repuestosSeleccionados.splice(index, 1);
            renderTablaDiagnosis();
        });

        function renderTablaDiagnosis() {
            let tbody = $('#tabla-repuestos-diagnosis tbody');
            let container = $('#hidden-inputs-repuestos');
            tbody.empty();
            container.empty();

            let total = 0;

            repuestosSeleccionados.forEach((r, i) => {
                let subtotal = r.cant * r.precio;
                total += subtotal;

                tbody.append(`
                    <tr>
                        <td style="font-size: 0.9rem;">${r.nombre}</td>
                        <td class="text-center">${r.cant}</td>
                        <td>$${r.precio.toFixed(2)}</td>
                        <td style="font-weight: 700;">$${subtotal.toFixed(2)}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-outline-danger btn-remove-diag" data-index="${i}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);

                container.append(`
                    <input type="hidden" name="repuestos[${i}][id]" value="${r.id}">
                    <input type="hidden" name="repuestos[${i}][cantidad]" value="${r.cant}">
                    <input type="hidden" name="repuestos[${i}][precio]" value="${r.precio}">
                `);
            });

            $('#total-piezas-diag').text('$' + total.toFixed(2));
        }
    });
</script>
@stop
