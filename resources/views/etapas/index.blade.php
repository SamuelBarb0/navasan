@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header d-flex justify-content-between align-items-center text-white py-3 rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-gear-fill me-2"></i> Etapas de Producción</h4>
            <button class="btn btn-light btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#modalCrearEtapa">
                <i class="bi bi-plus-circle"></i> Nueva Etapa
            </button>
        </div>

        <div class="card-body px-4 py-4 bg-light">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Orden</th>
                        <th>Usuario Asignado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($etapas as $etapa)
                        <tr>
                            <td>{{ $etapa->id }}</td>
                            <td>{{ $etapa->nombre }}</td>
                            <td>{{ $etapa->orden ?? '-' }}</td>
                            <td>{{ $etapa->responsable?->name ?? 'Sin asignar' }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditarEtapa{{ $etapa->id }}">
                                    <i class="bi bi-pencil-fill"></i> Editar
                                </button>
                                @include('etapas.partials.form-edit', ['etapa' => $etapa])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Crear --}}
@include('etapas.partials.form-create')
@endsection
