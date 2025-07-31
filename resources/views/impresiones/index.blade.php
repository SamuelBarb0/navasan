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
            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Mensaje adicional de advertencia --}}
            @if(session('warning_extra'))
                <div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                    {!! session('warning_extra') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning_extra_multiple'))
                @foreach(session('warning_extra_multiple') as $mensaje)
                    <div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                        {!! $mensaje !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endforeach
            @endif

            {{-- Tabla --}}
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
                                    <td>#{{ $item->orden->numero_orden ?? '—' }}</td>
                                    <td>{{ $item->tipo_impresion }}</td>
                                    <td>{{ $item->maquina }}</td>
                                    <td>{{ $item->cantidad_pliegos }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item->inicio_impresion)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($item->fin_impresion)
                                            {{ \Carbon\Carbon::parse($item->fin_impresion)->format('d/m/Y H:i') }}
                                        @else
                                            —
                                        @endif
                                    </td>
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

{{-- Toast container --}}
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
    {{-- Toasts por impresiones sin fecha de fin --}}
    @php
        $ordenesSinFin = $impresiones->filter(fn($i) => is_null($i->fin_impresion))
                                     ->pluck('orden.numero_orden')
                                     ->unique();
    @endphp

    @foreach ($ordenesSinFin as $index => $numeroOrden)
        <div class="toast align-items-center text-bg-warning border-0 mb-2 cursor-pointer"
             role="alert" aria-live="assertive" aria-atomic="true"
             data-bs-delay="6000" id="toastFin{{ $index }}"
             onclick="window.location.href='{{ route('impresiones.index') }}'">
            <div class="d-flex">
                <div class="toast-body">
                    ⚠️ La orden <strong>#{{ $numeroOrden }}</strong> aún no tiene registrada la <strong>fecha de fin de impresión</strong>.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    @endforeach

    {{-- Toasts por diferencias de pliegos --}}
    @php
        $diferencias = $impresiones->filter(function ($i) {
            return !is_null($i->cantidad_pliegos)
                && !is_null($i->cantidad_pliegos_impresos)
                && $i->cantidad_pliegos !== $i->cantidad_pliegos_impresos;
        });
    @endphp

    @foreach ($diferencias as $index => $i)
        @php
            $orden = $i->orden->numero_orden ?? 'N/A';
            $msg = $i->cantidad_pliegos_impresos > $i->cantidad_pliegos
                ? "⚠️ La orden <strong>#{$orden}</strong> tiene más pliegos impresos que los solicitados."
                : "⚠️ La orden <strong>#{$orden}</strong> tiene menos pliegos impresos que los solicitados.";
        @endphp
        <div class="toast align-items-center text-bg-warning border-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true"
             data-bs-delay="6000" id="toastPliegos{{ $index }}">
            <div class="d-flex">
                <div class="toast-body">
                    {!! $msg !!}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto"
                        data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    @endforeach
</div>


{{-- Script para mostrar los toasts automáticamente --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toastElements = document.querySelectorAll('.toast');
        toastElements.forEach(toastEl => {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        });
    });
</script>
@endsection
