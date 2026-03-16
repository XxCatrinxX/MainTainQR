@extends('adminlte::page')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>12</h3>
                <p>Órdenes Activas</p>
            </div>
            <div class="icon"><i class="fas fa-folder-open"></i></div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 class="text-white">5</h3>
                <p>Órdenes Pendientes</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>8</h3>
                <p>En Proceso</p>
            </div>
            <div class="icon"><i class="fas fa-sync-alt"></i></div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>120</h3>
                <p>Órdenes Completadas</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title text-bold">Resumen de Órdenes</h3>
            </div>
            <div class="card-body">
                <canvas id="orderChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title textbold">Órdenes Recientes</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Equipo</th>
                            <th>Estado</th>
                            <th>Técnico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ORD-1057</td>
                            <td>Laptop Lenovo</td>
                            <td><span class="badge badge-success">En Proceso</span></td>
                            <td>J. Torres</td>
                        </tr>
                        <tr>
                            <td>ORD-1049</td>
                            <td>Impresora Epson</td>
                            <td><span class="badge badge-warning">Pendiente</span   ></td>
                            <td>M. García</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-bold">Próximos Mantenimientos</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fas fa-laptop mr-2 text-primary"></i> Laptop Dell XPS - 25 May</li>
                    <li class="list-group-item"><i class="fas fa-printer mr-2 text-primary"></i> Impresora HP LaserJet - 30 May</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-bold text-danger">Alertas de Inventario</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-light border mb-2">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Piezas de Repuesto: Bajo Stock
                </div>
                <div class="alert alert-light border">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Aceite Lubricante: Nivel Bajo
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 text-right mb-4">
        <a href="{{ route('ordenes.create') }}" class="btn btn-success btn-lg shadow">
            <i class="fas fa-plus"></i> Nueva Orden
        </a>
    </div>
</div>
@endsection


@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('orderChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['L', 'M', 'M', 'J', 'V', 'S', 'D'],
            datasets: [{
                label: 'Órdenes Abiertas',
                data: [3, 5, 4, 7, 6, 8, 5],
                borderColor: '#007bff',
                fill: false,
                tension: 0.4
            }, {
                label: 'Órdenes Completadas',
                data: [1, 3, 2, 4, 7, 5, 2],
                borderColor: '#28a745',
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>
@stop