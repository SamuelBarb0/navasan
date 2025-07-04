@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-check2-square me-2"></i> Revisiones de Producción</h4>
            <button class="btn" style="background-color: #0578be; color: #ffff;" data-bs-toggle="modal" data-bs-target="#modalRegistrarRevision">
                <i class="bi bi-plus-circle"></i> Registrar Revisión
            </button>
        </div>

        <div class="card-body bg-light rounded-bottom-4">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($revisiones->isEmpty())
            <div class="alert alert-info">No hay revisiones registradas aún.</div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm">
                    <thead style="background-color: #7CB9E6;" class="text-dark">
                        <tr class="text-center">
                            <th>#</th>
                            <th>Orden</th>
                            <th>Revisado por</th>
                            <th>Cantidad Revisada</th>
                            <th>Tipo</th>
                            <th>Comentarios</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($revisiones as $rev)
                        @php
                        $badge = match($rev->tipo) {
                        'correcta' => 'success',
                        'defectos' => 'warning',
                        'apartada' => 'orange',
                        'rechazada' => 'danger',
                        default => 'secondary'
                        };
                        @endphp
                        <tr class="text-center">
                            <td>{{ $rev->id }}</td>
                            <td>#{{ $rev->orden_id }}</td>
                            <td>{{ $rev->usuario }}</td>
                            <td>{{ $rev->cantidad }}</td>
                            <td>
                                <span class="badge bg-{{ $badge }} text-capitalize">
                                    {{ ucfirst($rev->tipo) }}
                                </span>
                            </td>
                            <td>{{ $rev->comentarios ?? '—' }}</td>
                            <td>{{ $rev->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

@include('partials.modal-registrar')
@endsection