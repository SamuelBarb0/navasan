@extends('layouts.app')

@section('content')

@php
$usuario = auth()->user();
$esAdmin = $usuario->hasRole('administrador');
@endphp

<div class="container mt-5">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-file-text me-2"></i> Detalle de Orden: {{ $orden->numero_orden }}</h4>
            <a href="{{ route('ordenes.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left-circle"></i> Volver al listado
            </a>
        </div>

        <div class="card-body bg-light rounded-bottom-4">

            {{-- Información general --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong class="text-muted">Cliente:</strong> {{ $orden->cliente->nombre }}
                </div>
                <div class="col-md-3">
                    <strong class="text-muted">Fecha de recepción:</strong>
                    {{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}
                </div>
                <div class="col-md-3">
                    @php
                    $estadoColor = match($orden->estado) {
                        'pendiente' => 'secondary',
                        'en_proceso' => 'info',
                        'completado' => 'success',
                        'rechazado' => 'danger',
                        default => 'dark'
                    };
                    @endphp
                    <strong class="text-muted">Estado:</strong>
                    <span class="badge bg-{{ $estadoColor }} px-3 py-2 rounded-pill">
                        {{ ucfirst($orden->estado) }}
                    </span>
                </div>
            </div>

            {{-- Ítems / productos --}}
            <h5 class="mb-3 text-primary"><i class="bi bi-box-seam me-1"></i> Productos Solicitados</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle shadow-sm">
                    <thead style="background-color: #7CB9E6;" class="text-dark">
                        <tr>
                            <th>#</th>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <th>Cantidad Total</th>
                            <th>Entregas Programadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orden->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->producto->codigo ?? $item->nombre }}</td>
                            <td class="d-flex align-items-center gap-2">
                                @if($item->producto?->imagen)
                                    <img src="{{ asset($item->producto->imagen) }}" alt="img" width="100" height="100" class="rounded border">
                                @endif
                                <span>{{ $item->producto->nombre ?? $item->nombre }}</span>
                            </td>
                            <td>{{ $item->cantidad }}</td>
                            <td>
                                @if($item->entregas && $item->entregas->count())
                                    <ul class="mb-0 ps-3">
                                        @foreach($item->entregas as $entrega)
                                            <li>
                                                {{ \Carbon\Carbon::parse($entrega->fecha_entrega)->format('d/m/Y') }}
                                                —
                                                <strong>{{ $entrega->cantidad }}</strong> unidades
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">Sin entregas registradas</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($orden->etapas->isNotEmpty())
                <div class="alert alert-info d-flex align-items-center gap-2 rounded-3 shadow-sm py-3 px-4 mt-5" style="border-left: 5px solid #16509D;">
                    <i class="bi bi-exclamation-circle-fill fs-4 text-primary"></i>
                    <div>
                        <strong>Importante:</strong> Las etapas deben gestionarse en el orden establecido. Solo podrás iniciar la siguiente cuando la anterior esté finalizada.
                    </div>
                </div>
            @endif

            {{-- Ordenar por el campo "orden" de etapa_produccions --}}
            @php
                $etapasOrdenadas = $orden->etapas->sortBy(function($etapa) {
                    return $etapa->etapa?->orden ?? 999; // 999 por si alguna no tiene orden
                });
            @endphp

            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-3 align-middle shadow-sm">
                    <thead style="background-color: #f1f1f1;" class="text-dark">
                        <tr>
                            <th>Etapa</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Observaciones</th>
                            <th>Gestión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($etapasOrdenadas as $etapa)
                            @php
                                $nombreEtapa = $etapa->etapa?->nombre;
                                $color = match($etapa->estado) {
                                    'pendiente' => 'secondary',
                                    'en_proceso' => 'info',
                                    'completado' => 'success',
                                    'rechazado' => 'danger',
                                    default => 'dark'
                                };
                                // ✅ permite gestionar si:
                                // - es admin, o
                                // - el usuario que inició la etapa es el actual, o
                                // - el responsable en la plantilla (etapa_produccions.usuario_id) es el actual
                                $puedeGestionar = $esAdmin
                                    || $etapa->usuario_id === $usuario->id
                                    || ($etapa->etapa?->usuario_id === $usuario->id);
                            @endphp

                            <tr>
                                <td>{{ $nombreEtapa ?? '—' }}</td>
                                <td>{{ $etapa->usuario?->name ?? $etapa->etapa?->usuario?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $color }} px-3 py-2 rounded-pill">
                                        {{ ucfirst($etapa->estado) }}
                                    </span>
                                </td>
                                <td>{{ $etapa->inicio ? \Carbon\Carbon::parse($etapa->inicio)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $etapa->fin ? \Carbon\Carbon::parse($etapa->fin)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $etapa->observaciones ?? '—' }}</td>
                                <td>
                                    @if($puedeGestionar)
                                        @if($etapa->estado === 'pendiente')
                                            <form action="{{ route('orden_etapas.iniciar', $etapa->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-play-circle"></i> Iniciar
                                                </button>
                                            </form>
                                        @elseif($etapa->estado === 'en_proceso')
                                            <form action="{{ route('orden_etapas.finalizar', $etapa->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <div class="input-group input-group-sm">
                                                    <input type="text" name="observaciones" class="form-control" placeholder="Observaciones" required style="border-color: #7CB9E6;">
                                                    <button class="btn btn-success" type="submit">
                                                        <i class="bi bi-check-circle"></i> Finalizar
                                                    </button>
                                                </div>
                                            </form>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        @if($orden->insumos->isNotEmpty())
            <hr class="my-4">

            <h5 class="text-primary mb-3">
                <i class="bi bi-box-seam"></i> Insumos requeridos para esta orden
            </h5>

            <div class="table-responsive mt-2">
                <table class="table table-bordered align-middle shadow-sm">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Insumo</th>
                            <th>Descripción</th>
                            <th>Cantidad Requerida</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orden->insumos as $insumo)
                        <tr class="text-center">
                            <td>{{ $insumo->insumo->nombre }}</td>
                            <td>{{ $insumo->insumo->descripcion }}</td>
                            <td>{{ $insumo->cantidad_requerida }}</td>
                            <td>
                                <span class="badge bg-{{ match($insumo->estado) {
                                    'pendiente' => 'secondary',
                                    'liberado'  => 'success',
                                    'solicitado'=> 'warning',
                                    default     => 'dark'
                                } }}">
                                    {{ ucfirst($insumo->estado) }}
                                </span>
                            </td>
                            <td class="d-flex justify-content-center gap-2 flex-wrap">
                                @hasanyrole('almacen|administrador')
                                    <form action="{{ route('insumo_orden.actualizar_estado', $insumo->id) }}" method="POST" class="d-flex">
                                        @csrf @method('PATCH')
                                        <select name="estado" class="form-select form-select-sm me-2" required>
                                            <option value="pendiente" {{ $insumo->estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="liberado"  {{ $insumo->estado === 'liberado'  ? 'selected' : '' }}>Liberado</option>
                                            <option value="solicitado"{{ $insumo->estado === 'solicitado'? 'selected' : '' }}>Solicitado</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-save"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('ordenes.insumos.eliminar', $insumo->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este insumo de la orden?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted">Solo lectura</span>
                                @endhasanyrole
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($orden->estado !== 'completado')
        @hasanyrole('preprensa|administrador')
            <hr class="my-4">

            <h6 class="text-primary mb-3">
                <i class="bi bi-droplet-half text-danger"></i>
                Asignar nuevo insumo
            </h6>

            <div class="bg-white p-3 rounded shadow-sm border">
                <form action="{{ route('ordenes.insumos.agregar', $orden->id) }}" method="POST" class="row align-items-end g-3">
                    @csrf

                    <div class="col-md-5">
                        <label for="insumo_id" class="form-label fw-bold">Insumo existente</label>
                        <select name="insumo_id" id="insumo_id" class="form-select select-insumo" required>
                            <option value="">Seleccione un insumo</option>
                            @foreach(\App\Models\Insumo::all() as $insumo)
                                <option value="{{ $insumo->id }}">{{ $insumo->nombre }} ({{ $insumo->unidad }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="cantidad_requerida" class="form-label fw-bold">Cantidad requerida</label>
                        <input type="number" name="cantidad_requerida" id="cantidad_requerida" class="form-control" required min="1">
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Agregar
                        </button>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCrearInsumo">
                            <i class="bi bi-plus"></i> Nuevo insumo
                        </button>
                    </div>
                </form>
            </div>
        @endhasanyrole
    @endif

    @include('partials.crear-insumo')

@endsection
