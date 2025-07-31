@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header text-white py-3 rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0">
                <i class="bi bi-file-earmark-plus-fill me-2"></i>
                Crear Orden de Producción
            </h4>
        </div>

        <div class="card-body px-4 py-4" style="background-color: #f9fbfd;">

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger">
                <strong>¡Ups!</strong> Hubo algunos problemas con tus datos:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {!! session('warning') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif


            <form action="{{ route('ordenes.store') }}" method="POST">
                @csrf

                {{-- Cliente --}}
                <div class="mb-4">
                    <label for="cliente_id" class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="cliente_id" id="cliente_id" class="form-select select2" required style="border-color: #0578BE;">
                            <option value="">-- Selecciona un cliente --</option>
                            @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary" title="Ver clientes" style="border-color: #0578BE; color: #0578BE;">
                            <i class="bi bi-person-lines-fill"></i>
                        </a>
                    </div>
                </div>

                {{-- Número de orden y fecha --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="numero_orden" class="form-label fw-semibold">Número de Orden</label>
                        <input type="text" name="numero_orden" id="numero_orden" class="form-control" placeholder="Ej. ORD-2025-001" required style="border-color: #7CB9E6;">
                    </div>
                    <div class="col-md-6">
                        <label for="fecha" class="form-label fw-semibold">Fecha de Recepción</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" required style="border-color: #7CB9E6;">
                    </div>
                </div>

                {{-- Productos --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-semibold mb-0">Productos a Fabricar</label>
                    <button type="button" class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#modalProducto" style="border-color: #0578BE; color: #0578BE;">
                        <i class="bi bi-plus-circle"></i> Crear nuevo producto
                    </button>
                </div>

                <div id="items" class="mb-3 border rounded-3 p-3 bg-white">
                    <div class="producto-item row g-3 mb-3" data-index="0">
                        <div class="col-md-4">
                            <label class="form-label">Código del Producto</label>
                            <select name="items[0][producto_id]" class="form-select select2-producto" data-index="0" required style="border-color: #9EA1A2;">
                                <option value="">-- Selecciona código --</option>
                                @foreach($productos as $producto)
                                <option value="{{ $producto->id }}" data-nombre="{{ $producto->nombre }}">
                                    {{ $producto->codigo }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control nombre-producto" name="items[0][nombre]" readonly
                                style="background-color: #f1f1f1; border-color: #ccc;" placeholder="Nombre del producto">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cantidad Total</label>
                            <input type="number" class="form-control" name="items[0][cantidad]" required style="border-color: #9EA1A2;" placeholder="Ej. 1000">
                        </div>

                        <div class="col-12 mt-2">
                            <label class="form-label fw-semibold">Fechas de Entrega</label>
                            <div class="entregas" data-item-index="0">
                                <div class="row entrega-row g-2 mb-2" data-entrega-index="0">
                                    <div class="col-md-5">
                                        <input type="date" class="form-control" name="items[0][entregas][0][fecha]" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" class="form-control" name="items[0][entregas][0][cantidad]" placeholder="Cantidad a entregar" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeEntrega(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="addEntrega(this)" style="border-color: #0578BE; color: #0578BE;">
                                <i class="bi bi-calendar-plus"></i> Añadir fecha
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <button type="button" onclick="addItem()" class="btn btn-outline-primary btn-sm" style="border-color: #16509D; color: #16509D;">
                        <i class="bi bi-plus-circle"></i> Añadir otro producto
                    </button>
                </div>

                {{-- Etapas de Producción --}}
                <h5 class="form-label fw-semibold">Etapas de Producción</h5>

                <div class="row">
                    @forelse($etapas as $etapa)
                    <div class="col-md-6 mb-3">
                        <label for="etapa_{{ $etapa->id }}" class="w-100">
                            <div class="border rounded-3 p-3 d-flex align-items-center gap-3 shadow-sm bg-white">
                                <input type="checkbox"
                                    class="form-check-input mt-0"
                                    name="etapas[]"
                                    value="{{ $etapa->id }}"
                                    id="etapa_{{ $etapa->id }}"
                                    style="transform: scale(1.2);">
                                <div>
                                    <strong>{{ $etapa->nombre }}</strong><br>
                                    <small class="text-muted">
                                        Responsable: {{ $etapa->responsable?->name ?? 'Sin asignar' }}
                                    </small>
                                </div>
                            </div>
                        </label>
                    </div>
                    @empty
                    <div class="col-12">
                        <p class="text-muted">No hay etapas de producción configuradas.</p>
                    </div>
                    @endforelse
                </div>



                <div class="d-grid">
                    <button type="submit" class="btn btn-lg text-white" style="background-color: #0578BE;">
                        <i class="bi bi-check-circle me-1"></i> Guardar Orden
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Modal --}}
@include('productos.partials.modal-create')
@endsection