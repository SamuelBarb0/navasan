@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-printer-fill me-2"></i> Registros de Impresión</h4>
            <button class="btn btn-sm text-white" style="background-color: #0578BE;" data-bs-toggle="modal" data-bs-target="#modalRegistrarImpresion">
                <i class="bi bi-plus-circle me-1"></i> Nueva Impresión
            </button>
        </div>

        <div class="card-body bg-light rounded-bottom-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($impresiones->isEmpty())
                <div class="alert alert-info">No hay registros de impresión aún.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm">
                        <thead style="background-color: #7CB9E6;" class="text-dark text-center">
                            <tr>
                                <th>Orden</th>
                                <th>Tipo</th>
                                <th>Máquina</th>
                                <th>Pliegos</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($impresiones as $item)
                                @php
                                    $colores = [
                                        'espera' => 'warning',
                                        'proceso' => 'primary',
                                        'completado' => 'success',
                                        'rechazado' => 'danger',
                                    ];
                                @endphp
                                <tr class="text-center">
                                    <td>#{{ $item->orden_id }}</td>
                                    <td>{{ $item->tipo_impresion }}</td>
                                    <td>{{ $item->maquina }}</td>
                                    <td>{{ $item->cantidad_pliegos }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->inicio_impresion)->format('d/m/Y H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->fin_impresion)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $colores[$item->estado] ?? 'secondary' }}">
                                            {{ ucfirst($item->estado) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditarImpresion{{ $item->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Modal individual de edición --}}
                                @include('partials.editar-impresion', ['impresion' => $item])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal de creación --}}
@include('partials.crear-impresion')
@endsection
