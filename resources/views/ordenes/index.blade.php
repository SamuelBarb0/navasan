@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-clipboard-data me-2"></i> Órdenes de Producción</h4>
            <a href="{{ route('ordenes.create') }}" class="btn btn-sm text-white" style="background-color: #0578BE;">
                <i class="bi bi-plus-circle"></i> Nueva Orden
            </a>
        </div>

        <div class="card-body bg-light rounded-bottom-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($ordenes->isEmpty())
                <div class="alert alert-info">No hay órdenes registradas aún.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm">
                        <thead style="background-color: #7CB9E6;" class="text-dark">
                            <tr class="text-center">
                                <th>#</th>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ordenes as $orden)
                                <tr class="text-center">
                                    <td>{{ $orden->id }}</td>
                                    <td class="fw-semibold">{{ $orden->numero_orden }}</td>
                                    <td>{{ $orden->cliente->nombre }}</td>
                                    <td>{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $estadoColor = match($orden->estado) {
                                                'pendiente' => 'secondary',
                                                'en_proceso' => 'info',
                                                'completado' => 'success',
                                                'rechazado' => 'danger',
                                                default => 'dark'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $estadoColor }} px-3 py-2 rounded-pill text-capitalize">
                                            {{ str_replace('_', ' ', $orden->estado) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        {{-- Futuras acciones: editar / eliminar --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
