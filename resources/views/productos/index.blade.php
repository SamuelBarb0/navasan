@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow rounded-4 border-0">
        <div class="card-header text-white rounded-top-4 py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: #16509D;">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i> Lista de Productos</h5>
            <button class="btn btn-sm btn-light" onclick="abrirModalCrear()">
                <i class="bi bi-plus-circle"></i> Nuevo Producto
            </button>
        </div>

        <div class="card-body px-4 py-4" style="background-color: #f9fbfd;">
            {{-- Filtro por cliente --}}
            <form method="GET" action="{{ route('productos.index') }}" class="mb-4">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Filtrar por cliente:</label>
                        <select name="cliente_id" class="form-select">
                            <option value="">-- Todos los clientes --</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="bi bi-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>

            {{-- Mensajes --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- Tabla --}}
            @if($productos->isEmpty())
            <div class="alert alert-info">No hay productos registrados.</div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle shadow-sm text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Imagen</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Presentación</th>
                            <th>Unidad</th>
                            <th>Precio</th>
                            <th>Cliente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $producto)
                        <tr>
                            <td>
                                @if($producto->imagen)
                                    <img src="{{ asset($producto->imagen) }}" class="img-thumbnail" style="max-width: 80px;">
                                @else
                                    <span class="text-muted">Sin imagen</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $producto->codigo }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->presentacion ?? '-' }}</td>
                            <td>{{ $producto->unidad ?? '-' }}</td>
                            <td>${{ number_format($producto->precio, 2) }}</td>
                            <td>
                                @if($producto->cliente)
                                    <span class="badge bg-primary">{{ $producto->cliente->nombre }}</span>
                                @else
                                    <span class="badge bg-secondary">Sin asignar</span>
                                @endif
                            </td>
                            <td class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick='abrirModalEditar(@json($producto))'>
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <form action="{{ route('productos.destroy', $producto->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Eliminar
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

@include('productos.partials.modal-create')
@include('productos.partials.modal-edit')

<script>
    function abrirModalEditar(producto) {
        const form = document.getElementById('formEditarProducto');
        form.setAttribute('action', `/productos/${producto.id}`);

        // Setear campos
        form.querySelector('input[name="codigo"]').value = producto.codigo;
        form.querySelector('input[name="nombre"]').value = producto.nombre;
        form.querySelector('input[name="presentacion"]').value = producto.presentacion || '';
        form.querySelector('input[name="unidad"]').value = producto.unidad || '';
        form.querySelector('input[name="precio"]').value = producto.precio;
        form.querySelector('input[name="producto_id"]').value = producto.id;

        $('#producto_cliente_editar').val(producto.cliente_id).trigger('change');

        // Previsualizar imagen
        const preview = document.getElementById('previewImagen');
        if (producto.imagen) {
            preview.innerHTML = `<img src="/${producto.imagen}" class="img-fluid rounded mt-2" style="max-height: 150px;">`;
        } else {
            preview.innerHTML = '';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
        modal.show();
    }
</script>
<script>
    function abrirModalCrear() {
        // Limpiar formulario
        const form = document.getElementById('formNuevoProducto');
        form.reset();
        document.getElementById('producto_id').value = '';
        $('#producto_cliente').val('').trigger('change');
        document.getElementById('previewImagen').innerHTML = '';
        form.setAttribute('action', "{{ route('productos.store') }}");

        // Cambiar textos del modal
        document.getElementById('modalProductoTitulo').innerText = 'Crear Producto';
        document.getElementById('btnGuardarTexto').innerText = 'Guardar';

        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('modalProducto'));
        modal.show();
    }
</script>
@endsection
