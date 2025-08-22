@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $tipo    = $tipo ?? Str::beforeLast(Route::currentRouteName(), '.');
    $esSuaje = $tipo === 'suaje-corte';

    // bandera para deshabilitar el select si llega vacío
    $tieneOrdenes = isset($ordenes) && $ordenes->count() > 0;
@endphp

<!-- Modal Registrar Acabado -->
<div class="modal fade" id="modalRegistrarAcabado" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ $action ?? '#' }}">
            @csrf
            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header rounded-top-4 text-white" style="background-color: #003566;">
                    <h5 class="modal-title">Registrar Proceso</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body bg-light">
                    {{-- Orden --}}
                    <div class="mb-3">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" id="create_orden_id" class="form-select" required {{ $tieneOrdenes ? '' : 'disabled' }}>
                            <option value="">
                                {{ $tieneOrdenes ? 'Seleccione una orden' : 'No hay órdenes disponibles para esta etapa' }}
                            </option>
                            @forelse($ordenes as $orden)
                                <option value="{{ $orden->id }}" @selected(old('orden_id') == $orden->id)>
                                    {{ $orden->numero_orden }}
                                </option>
                            @empty
                                {{-- sin opciones si no hay órdenes --}}
                            @endforelse
                        </select>
                        @unless($tieneOrdenes)
                            <small class="text-muted">No hay órdenes en <em>pendiente</em> o <em>en proceso</em> para esta etapa.</small>
                        @endunless
                    </div>

                    @if($esSuaje)
                        {{-- SÓLO PARA SUAJE --}}
                        <div class="mb-3">
                            <label class="form-label">Cantidad Recibida</label>
                            <input type="number" name="cantidad_liberada" class="form-control"
                                   min="0" step="1" required value="{{ old('cantidad_liberada') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cantidad Final</label>
                            <input type="number" name="cantidad_pliegos_impresos" class="form-control"
                                   min="0" step="1" required value="{{ old('cantidad_pliegos_impresos') }}">
                        </div>
                    @else
                        {{-- Producto (según la orden) --}}
                        <div class="mb-3">
                            <label class="form-label">Producto</label>
                            <select name="producto_id" id="create_producto_id" class="form-select" required {{ $tieneOrdenes ? '' : 'disabled' }}>
                                <option value="">{{ $tieneOrdenes ? 'Seleccione primero una orden' : 'Sin órdenes disponibles' }}</option>
                            </select>
                            <small class="text-muted" id="ayudaProductos" style="display:none;">
                                Mostrando productos de la orden seleccionada.
                            </small>
                        </div>

                        {{-- Proceso --}}
                        <div class="mb-3">
                            <label class="form-label">Proceso</label>
                            <select name="proceso" class="form-select" required>
                                @foreach(($procesos ?? []) as $p)
                                    <option value="{{ $p }}" @selected(old('proceso') == $p)">
                                        {{ \Illuminate\Support\Str::of($p)->replace('_',' ')->title() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Realizado por --}}
                        <div class="mb-3">
                            <label class="form-label">Realizado por</label>
                            <input type="text" name="realizado_por" class="form-control"
                                   value="{{ old('realizado_por') }}" required>
                        </div>

                        {{-- Cantidad Recibida --}}
                        <div class="mb-3">
                            <label class="form-label">Cantidad Recibida</label>
                            <input type="number" name="cantidad_liberada" class="form-control"
                                   min="0" step="1" required value="{{ old('cantidad_liberada') }}">
                        </div>

                        {{-- Fecha fin (opcional) --}}
                        <div class="mb-3">
                            <label class="form-label">Fecha Fin (opcional)</label>
                            <input type="datetime-local" name="fecha_fin" class="form-control"
                                   value="{{ old('fecha_fin') }}">
                        </div>
                    @endif
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="submit" class="btn text-white" style="background-color: #0578BE;">
                        <i class="bi bi-check-circle"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(!$esSuaje)
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ordenSel = document.getElementById('create_orden_id');
    const prodSel  = document.getElementById('create_producto_id');
    const ayuda    = document.getElementById('ayudaProductos');

    const setDisabled = (el, on) => on ? el.setAttribute('disabled','disabled') : el.removeAttribute('disabled');

    const cargarProductosDeOrden = (ordenId) => {
        if (!ordenId) {
            prodSel.innerHTML = '<option value="">Seleccione primero una orden</option>';
            setDisabled(prodSel, true);
            ayuda.style.display = 'none';
            return;
        }

        prodSel.innerHTML = '<option value="">Cargando productos...</option>';
        setDisabled(prodSel, true);

        fetch(`/ordenes/${ordenId}/items-json`)
            .then(r => r.json())
            .then(items => {
                let options = '<option value="">Seleccione un producto</option>';
                (items || []).forEach(it => {
                    const pid  = it.producto_id ?? it.id;
                    const pnom = it.producto_nombre ?? it.nombre ?? `Producto ${pid}`;
                    options += `<option value="${pid}">${pnom}</option>`;
                });
                prodSel.innerHTML = options;

                const oldProd = @json(old('producto_id'));
                if (oldProd) prodSel.value = oldProd;

                setDisabled(prodSel, false);
                ayuda.style.display = 'inline';
            })
            .catch(() => {
                prodSel.innerHTML = '<option value="">Error al cargar productos</option>';
                setDisabled(prodSel, true);
                ayuda.style.display = 'none';
            });
    };

    // Si vienen órdenes, habilita el flujo
    const hayOrdenes = {{ $tieneOrdenes ? 'true' : 'false' }};
    if (hayOrdenes) {
        const oldOrden = @json(old('orden_id'));
        if (oldOrden) {
            ordenSel.value = oldOrden;
            cargarProductosDeOrden(oldOrden);
        }
        ordenSel.addEventListener('change', e => cargarProductosDeOrden(e.target.value));
    }
});
</script>
@endif
