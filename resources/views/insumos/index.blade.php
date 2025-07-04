@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center py-3 px-4">
            <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i> Insumos en Inventario</h4>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalCrearInsumo">
                <i class="bi bi-plus-circle me-1"></i> Nuevo Insumo
            </button>
        </div>

        <div class="card-body bg-white px-4 py-4">
            @if($insumos->isEmpty())
                <div class="alert alert-info">No hay insumos registrados actualmente.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle shadow-sm rounded">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Nombre</th>
                                <th>Unidad</th>
                                <th>Descripción</th>
                                <th>Cantidad actual</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($insumos as $insumo)
                                <tr>
                                    <td class="text-capitalize">{{ $insumo->nombre }}</td>
                                    <td class="text-center">{{ $insumo->unidad }}</td>
                                    <td class="text-muted">{{ $insumo->descripcion ?? '-' }}</td>
                                    <td class="text-center fw-bold">
                                        {{ number_format($insumo->inventario?->cantidad_disponible ?? 0, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $insumo->activo ? 'success' : 'secondary' }}">
                                            {{ $insumo->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editarInsumoModal{{ $insumo->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalRecepcionInsumo"
                                            onclick="setInsumoRecepcion({{ $insumo->id }})">
                                            <i class="bi bi-truck"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Modal editar insumo --}}
                                @include('partials.editar-insumo', ['insumo' => $insumo])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function setInsumoRecepcion(id) {
        document.getElementById('recepcion_insumo_id').value = id;
    }
</script>

{{-- Modales --}}
@include('partials.crear-insumo')
@include('partials.recepcion-insumo')
@endsection
