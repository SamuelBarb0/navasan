@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow rounded-4">
        <div class="card-header text-white rounded-top-4 d-flex justify-content-between align-items-center py-3 px-4"
            style="background-color: #16509D;">
            <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Insumos en Inventario</h4>
            <button class="btn btn-sm text-white" style="background-color: #0578BE;" data-bs-toggle="modal" data-bs-target="#modalCrearInsumo">
                <i class="bi bi-plus-circle me-1"></i> Nuevo Insumo
            </button>
        </div>

        <div class="card-body px-4 py-4" style="background-color: #F9FAFB;">
            
            {{-- Filtro por categoría --}}
            <form method="GET" action="{{ route('insumos.index') }}" class="mb-4 d-flex align-items-center gap-3">
                <label for="categoria_id" class="mb-0 fw-semibold">Filtrar por categoría:</label>
                <select name="categoria_id" id="categoria_id" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nombre }}
                        </option>
                    @endforeach
                </select>

                @if(request('categoria_id'))
                    <a href="{{ route('insumos.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar filtro</a>
                @endif
            </form>

            @if($insumos->isEmpty())
                <div class="alert" style="background-color: #7CB9E6; color: #16509D;">No hay insumos registrados actualmente.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle shadow-sm">
                        <thead style="background-color: #7CB9E6;" class="text-dark text-center">
                            <tr>
                                <th>Categoría</th>
                                <th>Nombre</th>
                                <th>Unidad</th>
                                <th>Descripción</th>
                                <th>Cantidad actual</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($insumos as $insumo)
                                <tr>
                                    <td class="text-center">{{ $insumo->categoria?->nombre ?? '-' }}</td>
                                    <td class="text-capitalize">{{ $insumo->nombre }}</td>
                                    <td class="text-center">{{ $insumo->unidad }}</td>
                                    <td class="text-muted">{{ $insumo->descripcion ?? '-' }}</td>
                                    <td class="text-center fw-bold">
                                        {{ number_format($insumo->inventario?->cantidad_disponible ?? 0, 2) }}
                                    </td>
                                    <td class="text-center d-flex justify-content-center gap-2">
                                        {{-- Editar --}}
                                        <button class="btn btn-sm" style="border: 1px solid #0578BE; color: #0578BE"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editarInsumoModal{{ $insumo->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        {{-- Recepción --}}
                                        <button class="btn btn-sm" style="border: 1px solid #9EA1A2; color: #9EA1A2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalRecepcionInsumo"
                                            onclick="setInsumoRecepcion({{ $insumo->id }})">
                                            <i class="bi bi-truck"></i>
                                        </button>

                                        {{-- Eliminar --}}
                                        <form id="form-eliminar-{{ $insumo->id }}" action="{{ route('insumos.destroy', $insumo) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <button class="btn btn-sm" style="border: 1px solid #dc3545; color: #dc3545"
                                            onclick="confirmarEliminacion({{ $insumo->id }}, '{{ $insumo->nombre }}')">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>

                                {{-- Modal editar insumo --}}
                                @include('partials.editar-insumo', ['insumo' => $insumo])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- JS --}}
<script>
    function setInsumoRecepcion(id) {
        document.getElementById('recepcion_insumo_id').value = id;
    }

    function confirmarEliminacion(id, nombre) {
        if (confirm(`¿Estás seguro de que deseas eliminar el insumo "${nombre}"? Esta acción no se puede deshacer.`)) {
            document.getElementById(`form-eliminar-${id}`).submit();
        }
    }
</script>

{{-- Modales --}}
@include('partials.crear-insumo')
@include('partials.recepcion-insumo')
@endsection
