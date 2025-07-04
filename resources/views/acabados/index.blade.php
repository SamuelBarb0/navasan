@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-scissors me-2"></i> Procesos de Acabado</h4>
            <button class="btn btn-sm text-white" style="background-color: #0578BE;" data-bs-toggle="modal" data-bs-target="#modalRegistrarAcabado">
                <i class="bi bi-plus-circle"></i> Nuevo Acabado
            </button>
        </div>

        <div class="card-body bg-light rounded-bottom-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($acabados->isEmpty())
                <div class="alert alert-info">No hay procesos de acabado registrados aún.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm">
                        <thead style="background-color: #7CB9E6;" class="text-dark">
                            <tr class="text-center">
                                <th>Orden</th>
                                <th>Proceso</th>
                                <th>Realizado por</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($acabados as $ac)
                                <tr class="text-center">
                                    <td>#{{ $ac->orden_id }}</td>
                                    <td class="fw-semibold">{{ $ac->proceso_nombre }}</td>
                                    <td>{{ $ac->realizado_por }}</td>
                                    <td>{{ $ac->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@include('partials.registrar-acabado')
@endsection
