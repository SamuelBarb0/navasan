@extends('layouts.app')

@section('content')
@php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Ej: suaje-corte.index -> suaje-corte.store / suaje-corte.update
$routeBase = Str::beforeLast(Route::currentRouteName(), '.');
$routeStore = $routeBase . '.store';
$routeUpdate = $routeBase . '.update';

// Detecta tipo por nombre de ruta
$routeName = Route::currentRouteName();
$tipo = Str::beforeLast($routeName, '.'); // 'suaje-corte' | 'laminado' | 'empalmado'
$esSuaje = $tipo === 'suaje-corte';
@endphp

<div class="container mt-5">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header text-white d-flex justify-content-between align-items-center rounded-top-4" style="background-color: #16509D;">
            <h4 class="mb-0">
                <i class="bi bi-scissors me-2"></i> {{ $titulo ?? 'Procesos de Acabado' }}
            </h4>
            <button class="btn btn-sm text-white" style="background-color: #0578BE;" data-bs-toggle="modal" data-bs-target="#modalRegistrarAcabado">
                <i class="bi bi-plus-circle"></i> Nuevo
            </button>
        </div>

        <div class="card-body bg-light rounded-bottom-4">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif {{-- /if success --}}

            @if($acabados->isEmpty())
            <div class="alert alert-info">No hay procesos registrados aún.</div>
            @else
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle shadow-sm">
                    <thead style="background-color: #7CB9E6;" class="text-dark">
                        <tr class="text-center">
                            <th>Orden</th>

                            @if($esSuaje)
                            <th>Cantidad liberada</th>
                            <th>Fecha</th>
                            @else
                            <th>Proceso</th>
                            <th>Realizado por</th>
                            <th>Producto</th>
                            <th>Pliegos impresos</th>
                            <th>Fecha</th>
                            <th>Fecha Fin</th>
                            @endif

                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($acabados as $ac)
                        <tr class="text-center">
                            <td>{{ $ac->orden->numero_orden ?? '—' }}</td>

                            @if($esSuaje)
                            <td>{{ $ac->cantidad_liberada ?? 0 }}</td>
                            <td>{{ $ac->created_at->format('d/m/Y H:i') }}</td>
                            @else
                            <td class="fw-semibold">{{ $ac->proceso_nombre ?? \Illuminate\Support\Str::of($ac->proceso)->replace('_',' ')->title() }}</td>
                            <td>{{ $ac->realizado_por }}</td>
                            <td>{{ $ac->producto->nombre ?? '—' }}</td>
                            <td>{{ $ac->cantidad_pliegos_impresos ?? '—' }}</td>
                            <td>{{ $ac->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $ac->fecha_fin ? \Carbon\Carbon::parse($ac->fecha_fin)->format('d/m/Y H:i') : '—' }}</td>
                            @endif

                            <td>
                                @if($esSuaje)
                                <button
                                    class="btn btn-sm btn-warning text-white"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarAcabado"
                                    onclick="cargarEdicion(
      {{ Js::from($ac->id) }},
      {{ Js::from($ac->orden_id) }},
      null,   // producto_id no aplica
      null,   // proceso no aplica
      null,   // realizado_por no aplica
      null,   // fecha_fin no aplica
      {{ Js::from($ac->cantidad_liberada) }},
      {{ Js::from($ac->cantidad_pliegos_impresos) }}  // <-- NUEVO
    )">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </button>

                                @else
                                <button
                                    class="btn btn-sm btn-warning text-white"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarAcabado"
                                    onclick="cargarEdicion(
                                                    {{ Js::from($ac->id) }},
                                                    {{ Js::from($ac->orden_id) }},
                                                    {{ Js::from($ac->producto_id) }},
                                                    {{ Js::from($ac->proceso) }},
                                                    {{ Js::from($ac->realizado_por) }},
                                                    {{ Js::from(optional($ac->fecha_fin)?->format('Y-m-d\TH:i')) }},
                                                    {{ Js::from($ac->cantidad_pliegos_impresos) }}
                                                )">
                                    <i class="bi bi-pencil-square"></i> Editar
                                </button>
                                @endif
                                <form action="{{ route($routeBase . '.destroy', $ac->id) }}"
                                    method="POST"
                                    style="display:inline-block"
                                    onsubmit="return confirm('¿Seguro que deseas eliminar este registro?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Borrar
                                    </button>
                                </form>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($acabados, 'links'))
            <div class="mt-3">
                {{ $acabados->links() }}
            </div>
            @endif {{-- /if links --}}
            @endif {{-- /if empty else --}}
        </div>
    </div>
</div>

<script>
    const updateUrlTemplate = @json(route($routeUpdate, ['id' => '__ID__']));
    const ES_SUAJE = @json($esSuaje);

    async function cargarProductosDeOrdenEdit(ordenId, selectedProductoId = null) {
        if (ES_SUAJE) return; // Suaje no usa productos

        const selProducto = document.getElementById('edit_producto_id');
        const ayuda = document.getElementById('edit_ayudaProductos');

        if (!ordenId) {
            selProducto.innerHTML = '<option value="">Seleccione primero una orden</option>';
            if (ayuda) ayuda.style.display = 'none';
            return;
        }
        selProducto.innerHTML = '<option value="">Cargando productos...</option>';

        try {
            const res = await fetch(`/ordenes/${ordenId}/items-json`);
            const items = await res.json();

            if (!Array.isArray(items) || items.length === 0) {
                selProducto.innerHTML = '<option value="">No hay productos en la orden</option>';
                if (selectedProductoId) selProducto.value = String(selectedProductoId);
                if (ayuda) ayuda.style.display = 'none';
                return;
            }

            let options = '<option value="">Seleccione un producto</option>';
            items.forEach(it => {
                const pid = it.producto_id ?? it.id;
                const pnom = it.producto_nombre ?? it.nombre ?? `Producto ${pid}`;
                const sel = (selectedProductoId && String(selectedProductoId) === String(pid)) ? 'selected' : '';
                options += `<option value="${pid}" ${sel}>${pnom}</option>`;
            });
            selProducto.innerHTML = options;
            if (ayuda) ayuda.style.display = 'inline';
        } catch (err) {
            selProducto.innerHTML = '<option value="">Error al cargar productos</option>';
            if (ayuda) ayuda.style.display = 'none';
        }
    }

    function cargarEdicion(
        id,
        orden_id,
        producto_id,
        proceso,
        realizado_por,
        fecha_fin,
        cantidad, // para suaje: cantidad_liberada
        cantidadPliegosSuaje // <-- NUEVO (opcional)
    ) {
        const form = document.getElementById('formEditarAcabado');
        const selOrden = document.getElementById('edit_orden_id');
        form.action = updateUrlTemplate.replace('__ID__', id);

        document.getElementById('edit_id').value = id ?? '';
        selOrden.value = orden_id ?? '';

        if (ES_SUAJE) {
            const inputCant = document.getElementById('edit_cantidad_liberada');
            if (inputCant) inputCant.value = (cantidad ?? 0);

            const inputPlieg = document.getElementById('edit_cantidad_pliegos_impresos_suaje');
            if (inputPlieg) inputPlieg.value = (cantidadPliegosSuaje ?? '');
            return;
        }

        // Laminado / Empalmado (igual que antes)
        document.getElementById('edit_proceso').value = proceso ?? '';
        document.getElementById('edit_realizado_por').value = realizado_por ?? '';
        document.getElementById('edit_cantidad_pliegos_impresos').value = (cantidad ?? '') === null ? '' : (cantidad ?? '');
        document.getElementById('edit_fecha_fin').value = fecha_fin ?? '';

        cargarProductosDeOrdenEdit(selOrden.value, producto_id);
        selOrden.onchange = (e) => cargarProductosDeOrdenEdit(e.target.value, null);
    }
</script>

{{-- Pasamos $tipo para que los parciales sepan si es suaje --}}
@include('partials.registrar-acabado', [
'action' => route($routeStore),
'procesos' => $procesos ?? [],
'ordenes' => $ordenes ?? null,
'productos' => $productos ?? null,
'tipo' => $tipo
])

@include('partials.editar-acabado', [
'procesos' => $procesos ?? [],
'ordenes' => $ordenes ?? null,
'productos' => $productos ?? null,
'tipo' => $tipo
])
@endsection