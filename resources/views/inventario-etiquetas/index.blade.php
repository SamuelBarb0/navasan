@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4 border-0">
        <div class="card-header text-white rounded-top-4 py-3 px-4" style="background-color: #16509D;">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Inventario de Etiquetas Excedentes</h5>
        </div>

        <div class="card-body bg-white px-4 py-4">

            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('inventario-etiquetas.store') }}" class="mb-4">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" class="form-select" required>
                            <option value="">Seleccione</option>
                            @foreach($ordenes as $orden)
                            <option value="{{ $orden->id }}">#{{ $orden->numero_orden }} - {{ $orden->cliente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Cantidad Excedente</label>
                        <input type="number" name="cantidad" class="form-control" required min="1">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Fecha programada entrega</label>
                        <input type="date" name="fecha_programada" class="form-control">
                    </div>

                    <div class="col-md-2 text-end">
                        <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                            <i class="bi bi-save me-1"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>

            <hr>

            <h6 class="text-secondary mb-3">Etiquetas registradas:</h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Orden</th>
                            <th>Cliente</th>
                            <th>Cantidad</th>
                            <th>Observaciones</th>
                            <th>Programada para</th>
                            <th>Creado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventarios as $item)
                        <tr>
                            <td><strong>#{{ $item->orden->numero_orden }}</strong></td>
                            <td>{{ $item->orden->cliente->nombre ?? '-' }}</td>
                            <td>{{ $item->cantidad }}</td>
                            <td>{{ $item->observaciones ?? '-' }}</td>
                            <td>
                                @if($item->fecha_programada)
                                <span class="badge bg-warning text-dark">{{ \Carbon\Carbon::parse($item->fecha_programada)->format('Y-m-d') }}</span>
                                @else
                                <span class="text-muted">No definida</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditar{{ $item->id }}">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </button>

                                @include('partials.modal-editar', ['etiqueta' => $item])
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay registros aún.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection