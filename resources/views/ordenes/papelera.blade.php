@extends('adminlte::page')

@section('title', 'Papelera de Órdenes')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Órdenes Archivadas</h3>
    </div>

    <div class="card-body table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Archivado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach($ordenes as $orden)
                <tr>
                    <td>{{ $orden->folio }}</td>
                    <td>{{ $orden->equipo->cliente->nombre ?? '' }}</td>
                    <td>{{ $orden->estado }}</td>
                    <td>{{ $orden->deleted_by }}</td>

                    <td>
                        <form method="POST" action="{{ route('ordenes.restore', $orden->id) }}">
                            @csrf
                            <button class="btn btn-success btn-sm">
                                Restaurar
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>
@stop