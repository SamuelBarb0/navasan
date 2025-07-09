@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4 border-0">
        <div class="card-header text-white rounded-top-4 py-3 px-4" style="background-color: #16509D;">
            <h5 class="mb-0"><i class="bi bi-arrow-counterclockwise me-2"></i>Devoluciones por parte del Cliente</h5>
        </div>

        <div class="card-body bg-white px-4 py-4">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('devoluciones.store') }}" class="mb-4">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" class="form-select" required>
                            <option value="">Seleccione</option>
                            @foreach($ordenes as $orden)
                                <option value="{{ $orden->id }}">#{{ $orden->numero_orden }} - {{ $orden->cliente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Revisadora Asignada</label>
                        <input type="text" name="revisadora_asignada" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo de Error</label>
                        <input type="text" name="tipo_error" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Motivo de Devolución (Cliente)</label>
                        <textarea name="motivo_cliente" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Comentarios Adicionales</label>
                        <textarea name="comentarios_adicionales" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="col-md-6 d-flex align-items-center">
                        <input type="checkbox" name="codigo_rojo" value="1" class="form-check-input me-2" id="urgente">
                        <label for="urgente" class="form-check-label"> Marcar como urgente (Código Rojo)</label>
                    </div>

                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                            <i class="bi bi-save me-1"></i> Registrar Devolución
                        </button>
                    </div>
                </div>
            </form>

            <hr>

            <h6 class="text-secondary mb-3">Historial de Devoluciones:</h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Orden</th>
                            <th>Cliente</th>
                            <th>Motivo</th>
                            <th>Revisadora</th>
                            <th>Urgente</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devoluciones as $item)
                        <tr>
                            <td>#{{ $item->orden->numero_orden }}</td>
                            <td>{{ $item->orden->cliente->nombre ?? '-' }}</td>
                            <td>{{ Str::limit($item->motivo_cliente, 40) }}</td>
                            <td>{{ $item->revisadora_asignada }}</td>
                            <td>
                                @if($item->codigo_rojo)
                                    <span class="badge bg-danger">Código Rojo</span>
                                @else
                                    <span class="badge bg-secondary">Normal</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay devoluciones registradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection
