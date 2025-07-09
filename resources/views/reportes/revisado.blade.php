@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4 border-0">
        <div class="card-header text-white rounded-top-4 py-3 px-4" style="background-color: #16509D;">
            <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i> Reporte Semanal - Área de Revisado</h5>
        </div>

        <div class="card-body bg-white px-4 py-4">
            <p class="mb-3 text-muted">Semana del <strong>{{ $start->format('d/m/Y') }}</strong> al <strong>{{ $end->format('d/m/Y') }}</strong></p>

            @if($reporte->isEmpty())
                <div class="alert alert-info">No se han registrado revisiones esta semana.</div>
            @else
                @foreach($reporte as $fila)
                    <div class="mb-4 border-bottom pb-3">
                        <h5 class="mb-3"><i class="bi bi-person-fill"></i> {{ $fila->revisado_por }}</h5>
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <span class="badge bg-primary">Órdenes normales: {{ $fila->ordenes_normales }}</span>
                            <span class="badge bg-danger">Órdenes urgentes: {{ $fila->ordenes_urgentes }}</span>
                            <span class="badge bg-success">Total cantidades revisadas: {{ $fila->total_revisado }}</span>
                        </div>

                        @if($fila->detalles->isEmpty())
                            <p class="text-muted">Sin detalle de órdenes revisadas.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-center align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Número Orden</th>
                                            <th>Fecha</th>
                                            <th>Urgente</th>
                                            <th>Cantidad Revisada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fila->detalles as $orden)
                                            <tr>
                                                <td>{{ $orden->numero_orden }}</td>
                                                <td>{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</td>
                                                <td>
                                                    @if($orden->urgente)
                                                        <span class="badge bg-danger">Sí</span>
                                                    @else
                                                        <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>
                                                <td>{{ $orden->cantidad_revisada }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
