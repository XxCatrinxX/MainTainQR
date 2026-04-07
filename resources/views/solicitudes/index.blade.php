@extends('adminlte::page')

@section('title', 'Solicitudes de Compra')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-shopping-cart mr-2 text-secondary"></i>Solicitudes de Compra</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
            <li class="breadcrumb-item active">Solicitudes</li>
        </ol>
    </div>
@stop

@section('content')
<div class="card card-outline card-primary shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4">Folio / Equipo</th>
                        <th>Pieza Solicitada</th>
                        <th class="text-center">Cant.</th>
                        <th>Solicitante</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-right px-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $s)
                    <tr>
                        <td class="px-4">
                            <a href="{{ route('ordenes.show', $s->orden_servicio_id) }}" style="font-weight: 700; color: #111827;">
                                {{ $s->ordenServicio->folio }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $s->ordenServicio->equipo->marca }} {{ $s->ordenServicio->equipo->modelo }}</small>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #374151;">{{ $s->nombre_pieza }}</span>
                            @if($s->descripcion)
                                <br><small class="text-muted">{{ $s->descripcion }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $s->cantidad }}</td>
                        <td>
                            <i class="fas fa-user-circle mr-1 text-muted"></i> {{ $s->usuario->nombre ?? 'Sistema' }}
                        </td>
                        <td class="text-muted" style="font-size: 0.85rem;">
                            {{ $s->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            @php
                                $badgeClass = [
                                    'pendiente' => 'badge-warning',
                                    'surtido' => 'badge-success',
                                    'cancelado' => 'badge-danger'
                                ][$s->estado] ?? 'badge-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1" style="border-radius: 6px; font-weight: 600;">
                                {{ ucfirst($s->estado) }}
                            </span>
                        </td>
                        <td class="text-right px-4">
                            @if($s->estado === 'pendiente')
                            <button type="button" class="btn btn-sm btn-success shadow-sm btn-surtir-modal" 
                                style="border-radius: 6px;"
                                data-id="{{ $s->id }}"
                                data-nombre="{{ $s->nombre_pieza }}"
                                data-cantidad="{{ $s->cantidad }}">
                                <i class="fas fa-check mr-1"></i> Surtir
                            </button>
                            @else
                                <span class="text-muted" style="font-size: 0.85rem;">Completado</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 d-block opacity-25"></i>
                            No hay solicitudes de compra pendientes.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($solicitudes->hasPages())
    <div class="card-footer bg-white border-top-0">
        {{ $solicitudes->links() }}
    </div>
    @endif
</div>

{{-- MODAL SURTIR --}}
<div class="modal fade" id="modalSurtir" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header">
                <h5 class="modal-title">Surtir Pieza: <span id="span-nombre-pieza"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-surtir" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Cant. Recibida</label>
                            <input type="number" name="cantidad_recibida" id="input-cantidad" class="form-control" required min="1">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Precio Venta ($)</label>
                            <input type="number" name="precio_venta" class="form-control" step="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>SKU (Opcional)</label>
                            <input type="text" name="sku" class="form-control" placeholder="Generar auto...">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Calidad</label>
                            <select name="calidad" class="custom-select">
                                <option value="genérica">Genérica</option>
                                <option value="original">Original</option>
                                <option value="oem">OEM</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar en Inventario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.btn-surtir-modal').click(function() {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');
            let cantidad = $(this).data('cantidad');

            $('#span-nombre-pieza').text(nombre);
            $('#input-cantidad').val(cantidad);
            $('#form-surtir').attr('action', `/solicitudes/${id}/surtir`);
            $('#modalSurtir').modal('show');
        });
    });
</script>
@stop

@section('css')
    {{-- Estilos centralizados en admin-custom.css --}}
@stop
