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
                            <th>Fecha Fin</th> <!-- NUEVO -->
                            <th>Acciones</th> <!-- NUEVO -->
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($acabados as $ac)
                        <tr class="text-center">
                            <td>{{ $ac->orden->numero_orden ?? '—' }}</td>
                            <td class="fw-semibold">{{ $ac->proceso_nombre }}</td>
                            <td>{{ $ac->realizado_por }}</td>
                            <td>{{ $ac->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $ac->fecha_fin ? \Carbon\Carbon::parse($ac->fecha_fin)->format('d/m/Y H:i') : '—' }}
                            </td> <!-- NUEVO -->
                            <td>
                            <button class="btn btn-sm btn-warning text-white"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditarAcabado"
                                onclick="cargarEdicion({{ $ac->id }}, '{{ $ac->orden_id }}', '{{ $ac->proceso }}', '{{ $ac->realizado_por }}', '{{ $ac->fecha_fin }}')">
                                <i class="bi bi-pencil-square"></i> Editar
                            </button>
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

<script>
    function cargarEdicion(id, orden_id, proceso, realizado_por, fecha_fin) {
        const form = document.getElementById('formEditarAcabado');
        form.action = '/acabados/' + id;

        document.getElementById('edit_id').value = id;
        document.getElementById('edit_orden_id').value = orden_id;
        document.getElementById('edit_proceso').value = proceso;
        document.getElementById('edit_realizado_por').value = realizado_por;
        document.getElementById('edit_fecha_fin').value = fecha_fin ? fecha_fin.replace(' ', 'T') : '';
    }
</script>

@include('partials.registrar-acabado')
@include('partials.editar-acabado')
@endsection