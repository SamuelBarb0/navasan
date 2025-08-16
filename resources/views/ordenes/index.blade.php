@extends('layouts.app')

@section('content')
<div class="container mt-5">
    {{-- Búsqueda inteligente --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header text-white rounded-top-4" style="background-color: #0A3965;">
            <h5 class="mb-0"><i class="bi bi-search me-2"></i> Búsqueda Inteligente por Código o Número de Orden</h5>
        </div>
        <div class="card-body bg-light rounded-bottom-4">
            <form method="GET" action="{{ route('ordenes.index') }}">
                <div class="row align-items-center">
                    <div class="col-md-10">
                        <input type="text" name="busqueda" class="form-control" placeholder="Ingrese código de producto o número de orden..." value="{{ request('busqueda') }}">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Órdenes de Producción --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-clipboard-data me-2"></i> Órdenes de Producción</h4>
            @hasanyrole('administrador|preprensa')
            <a href="{{ route('ordenes.create') }}" class="btn btn-sm text-white" style="background-color: #0578BE;">
                <i class="bi bi-plus-circle"></i> Nueva Orden
            </a>
            @endhasanyrole
        </div>

        <div class="card-body bg-light rounded-bottom-4">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($ordenes->isEmpty())
            <div class="alert alert-info">No hay órdenes registradas con los criterios actuales.</div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm">
                    <thead style="background-color: #7CB9E6;" class="text-dark">
                        <tr class="text-center">
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
                            <td class="d-flex justify-content-center gap-2">
                                <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <form action="{{ route('ordenes.destroy', $orden->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta orden?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    {{-- Devoluciones Urgentes (solo administrador) --}}
    @role('administrador')
    <div class="card border-0 shadow-sm rounded-4 mt-5">
        <div class="card-header text-white d-flex align-items-center rounded-top-4" style="background-color: #B02A37;">
            <h4 class="mb-0"><i class="bi bi-arrow-counterclockwise me-2"></i> Devoluciones Urgentes</h4>
        </div>

        <div class="card-body bg-light rounded-bottom-4">
            @if($devoluciones->isEmpty())
            <div class="alert alert-info">No hay devoluciones urgentes registradas.</div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm">
                    <thead style="background-color: #f8d7da;" class="text-dark">
                        <tr class="text-center">
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Motivo de Devolución</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devoluciones as $orden)
                        <tr class="text-center" style="background-color: #f8d7da;">
                            <td class="fw-semibold text-danger">{{ $orden->numero_orden }}</td>
                            <td>{{ $orden->cliente->nombre }}</td>
                            <td>{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</td>
                            <td>{{ $orden->comentarios ?? '—' }}</td>
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
                            <td class="d-flex justify-content-center gap-2">
                                <a href="{{ route('ordenes.show', $orden->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                @hasanyrole('administrador|preprensa')
                                <form action="{{ route('ordenes.destroy', $orden->id) }}" method="POST" onsubmit="return confirm('⚠️ ¿Eliminar esta orden de producción?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                                @endhasanyrole
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endrole

    @endsection