@extends('layouts.app')

@section('content')
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
                            <th>Producto</th>
                            <th>Cantidad Total</th>
                            <th>Entregas Programadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orden->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->nombre }}</td>
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

            {{-- Progreso por Etapas --}}
            <div class="mt-5">
                <h5 class="text-primary"><i class="bi bi-gear-wide-connected me-1"></i> Progreso por Etapas</h5>

                @if($orden->etapas->isEmpty())
                <div class="alert alert-warning mt-3">No hay etapas registradas para esta orden.</div>
                @else
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
                            @foreach($orden->etapas as $etapa)
                            @php
                            $color = match($etapa->estado) {
                            'pendiente' => 'secondary',
                            'en_proceso' => 'info',
                            'completado' => 'success',
                            'rechazado' => 'danger',
                            default => 'dark'
                            };
                            @endphp
                            <tr>
                                <td>{{ $etapa->etapa?->nombre ?? '—' }}</td>
                                <td>{{ $etapa->usuario?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $color }} px-3 py-2 rounded-pill">
                                        {{ ucfirst($etapa->estado) }}
                                    </span>
                                </td>
                                <td>{{ $etapa->inicio ? \Carbon\Carbon::parse($etapa->inicio)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $etapa->fin ? \Carbon\Carbon::parse($etapa->fin)->format('d/m/Y H:i') : '—' }}</td>
                                <td>{{ $etapa->observaciones ?? '—' }}</td>
                                <td>
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
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Insumos requeridos --}}
            <div class="mt-5">
                <h5 class="text-primary"><i class="bi bi-droplet-half me-1"></i> Insumos Requeridos</h5>

                @if($orden->insumos->isEmpty())
                <div class="alert alert-warning mt-3">No se han asignado insumos a esta orden.</div>
                @else
                <div class="table-responsive mt-3">
                    <table class="table table-bordered align-middle shadow-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Insumo</th>
                                <th>Cantidad Requerida</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orden->insumos as $insumo)
                            <tr>
                                <td>{{ $insumo->insumo->nombre }}</td>
                                <td>{{ $insumo->cantidad_requerida }}</td>
                                <td>
                                    <span class="badge bg-{{ match($insumo->estado) {
                                'pendiente' => 'secondary',
                                'liberado' => 'success',
                                'solicitado' => 'warning',
                                default => 'dark'
                            } }}">
                                        {{ ucfirst($insumo->estado) }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('insumo_orden.actualizar_estado', $insumo->id) }}" method="POST" class="d-flex">
                                        @csrf @method('PATCH')
                                        <select name="estado" class="form-select form-select-sm me-2" required>
                                            <option value="pendiente" {{ $insumo->estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="liberado" {{ $insumo->estado === 'liberado' ? 'selected' : '' }}>Liberado</option>
                                            <option value="solicitado" {{ $insumo->estado === 'solicitado' ? 'selected' : '' }}>Solicitado</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-save"></i> Guardar
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

            @if($orden->estado !== 'completado')
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
            @endif


            @include('partials.crear-insumo')


            @endsection