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

            {{-- Formulario de Registro --}}
            <form method="POST" action="{{ route('inventario-etiquetas.store') }}" class="mb-4">
                @csrf
                <div class="row g-3">
                    {{-- Orden --}}
                    <div class="col-md-4">
                        <label class="form-label">Orden de Producción (opcional)</label>
                        <select name="orden_id" class="form-select" id="ordenSelect">
                            <option value="">Sin orden</option>
                            @foreach($ordenes as $orden)
                            <option value="{{ $orden->id }}">#{{ $orden->numero_orden }} - {{ $orden->cliente->nombre }}</option>
                            @endforeach
                        </select>
                    </div>



                    {{-- Producto libre --}}
                    <div class="col-md-4" id="grupoProductoLibre" style="display: block;">
                        <label class="form-label">Producto (sin orden)</label>
                        <select name="producto_id" id="productoLibreSelect" class="form-select">
                            <option value="">Seleccione un producto</option>
                            @foreach($productos as $producto)
                            <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Producto de orden --}}
                    <div class="col-md-4" id="grupoItemOrden" style="display: none;">
                        <label class="form-label">Producto (de orden)</label>
                        <select name="item_orden_id" id="itemOrdenSelect" class="form-select">
                            <option value="">Seleccione un producto</option>
                        </select>
                    </div>

                    {{-- Cantidad --}}
                    <div class="col-md-4">
                        <label class="form-label">Cantidad Excedente</label>
                        <input type="number" name="cantidad" class="form-control" required min="1">
                    </div>

                    {{-- Fecha --}}
                    <div class="col-md-4">
                        <label class="form-label">Fecha programada entrega</label>
                        <input type="date" name="fecha_programada" class="form-control">
                    </div>

                    {{-- Observaciones --}}
                    <div class="col-md-8">
                        <label class="form-label">Observaciones</label>
                        <input type="text" name="observaciones" class="form-control" maxlength="1000">
                    </div>

                    {{-- Botón --}}
                    <div class="col-md-12 text-end mt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>

            <hr>

            {{-- Tabla de registros --}}
            <h6 class="text-secondary mb-3">Etiquetas registradas:</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Orden</th>
                            <th>Producto</th>
                            <th>Cliente</th>
                            <th>Cantidad</th>
                            <th>Observaciones</th>
                            <th>Programada para</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventarios as $item)
                        <tr>
                            <td>
                                @if($item->orden)
                                <strong>#{{ $item->orden->numero_orden }}</strong>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($item->itemOrden)
                                {{ $item->itemOrden->nombre }}
                                @elseif($item->producto)
                                {{ $item->producto->nombre }}
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $item->orden->cliente->nombre ?? '—' }}</td>
                            <td>{{ $item->cantidad }}</td>
                            <td>{{ $item->observaciones ?? '—' }}</td>
                            <td>
                                @if($item->fecha_programada)
                                <span class="badge bg-warning text-dark">{{ \Carbon\Carbon::parse($item->fecha_programada)->format('Y-m-d') }}</span>
                                @else
                                <span class="text-muted">No definida</span>
                                @endif
                            </td>
                            <td>
                                @php
                                $badgeClass = match($item->estado) {
                                'liberado' => 'bg-success',
                                'stock' => 'bg-info text-dark',
                                'pendiente' => 'bg-secondary',
                                default => 'bg-light text-muted'
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($item->estado ?? 'sin estado') }}</span>
                            </td>
                            <td>{{ $item->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditar{{ $item->id }}">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </button>
                                {{-- Botón eliminar --}}
                                <form id="form-eliminar-{{ $item->id }}" action="{{ route('inventario-etiquetas.destroy', $item) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminacionEtiqueta({{ $item->id }}, '{{ $item->id }}')">
                                    <i class="bi bi-trash-fill"></i> Eliminar
                                </button>

                                @include('partials.modal-editar', ['etiqueta' => $item])
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No hay registros aún.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
<script>
    function confirmarEliminacionEtiqueta(id, nombre) {
        if (confirm(`¿Estás seguro de que deseas eliminar el registro #${nombre}? Esta acción no se puede deshacer.`)) {
            document.getElementById(`form-eliminar-${id}`).submit();
        }
    }
</script>
<script>
    const ordenSelect = document.getElementById('ordenSelect');
    const grupoItemOrden = document.getElementById('grupoItemOrden');
    const grupoProductoLibre = document.getElementById('grupoProductoLibre');
    const itemOrdenSelect = document.getElementById('itemOrdenSelect');

    ordenSelect.addEventListener('change', function() {
        const ordenId = this.value;

        if (!ordenId) {
            // Mostrar productos sin orden
            grupoItemOrden.style.display = 'none';
            grupoProductoLibre.style.display = 'block';
            return;
        }

        // Mostrar productos de la orden
        grupoItemOrden.style.display = 'block';
        grupoProductoLibre.style.display = 'none';
        itemOrdenSelect.innerHTML = '<option value="">Cargando productos...</option>';

        fetch(`/ordenes/${ordenId}/items-json`)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Seleccione un producto</option>';
                data.forEach(p => {
                    options += `<option value="${p.id}">${p.nombre}</option>`;
                });
                itemOrdenSelect.innerHTML = options;
            })
            .catch(() => {
                itemOrdenSelect.innerHTML = '<option value="">Error al cargar</option>';
            });
    });
</script>
@endsection