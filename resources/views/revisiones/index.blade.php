@extends('layouts.app')

<style>
    .bg-orange-custom {
        background-color: #f7931e !important;
        color: #fff;
    }
</style>

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

            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
                <div id="toastAlertaRevision" class="toast align-items-center text-bg-warning border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body fw-bold">
                            ⚠️ Por favor revisar revisión de <span id="ordenAlerta"></span>.
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                    </div>
                </div>
            </div>


            @if($revisiones->isEmpty())
            <div class="alert alert-info">No hay revisiones registradas aún.</div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm">
                    <thead style="background-color: #7CB9E6;" class="text-dark">
                        <tr class="text-center">
                            <th>Orden</th>
                            <th>Revisado por</th>
                            <th>Cantidad Revisada</th>
                            <th>Tipo</th>
                            <th>Comentarios</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($revisiones as $rev)
                        @php
                        $badgeClass = match($rev->tipo) {
                        'correcta' => 'success',
                        'defectos' => 'warning',
                        'apartada' => 'apartada-custom', // clase personalizada
                        'rechazada' => 'danger',
                        default => 'secondary'
                        };
                        @endphp
                        <tr class="text-center">
                            <td>{{ $rev->orden->numero_orden ?? '—' }}</td>
                            <td>{{ $rev->revisado_por }}</td>
                            <td>{{ $rev->cantidad }}</td>
                            <td>
                                <span class="badge {{ $rev->tipo === 'apartada' ? 'bg-orange-custom text-white' : 'bg-' . $badgeClass }}">
                                    {{ $rev->tipo === 'apartada' ? 'Pausada a la espera de aprobación' : ucfirst($rev->tipo) }}
                                </span>
                            </td>
                            <td>{{ $rev->comentarios ?? '—' }}</td>
                            <td>{{ $rev->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEditarRevision{{ $rev->id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger me-1" data-bs-toggle="modal" data-bs-target="#modalEliminarRevision{{ $rev->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <form action="{{ route('revisiones.alerta', $rev->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
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
</div>

{{-- Modales individuales --}}
@include('revisiones.partials._modal_edit')
@include('revisiones.partials._modal_delete')
@include('partials.modal-registrar')

<script>
    function mostrarAlerta(nombreOrden) {
        const toastEl = document.getElementById('toastAlertaRevision');
        const ordenSpan = document.getElementById('ordenAlerta');
        ordenSpan.textContent = nombreOrden;
        const toast = new bootstrap.Toast(toastEl, {
            delay: 5000
        });
        toast.show();
    }
</script>

@endsection