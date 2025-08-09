@php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
// Detecta tipo; si no viene, lo saca de la ruta
$tipo = $tipo ?? Str::beforeLast(Route::currentRouteName(), '.');
$esSuaje = $tipo === 'suaje-corte';
@endphp

<!-- Modal Editar Acabado -->
<div class="modal fade" id="modalEditarAcabado" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="formEditarAcabado">
            @csrf
            @method('PUT')
            <div class="modal-content rounded-4 shadow-sm border-0">
                <div class="modal-header rounded-top-4 text-white" style="background-color: #ffc107;">
                    <h5 class="modal-title text-dark">Editar Proceso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body bg-light">
                    <input type="hidden" name="id" id="edit_id">

                    {{-- Orden --}}
                    <div class="mb-3">
                        <label class="form-label">Orden de Producción</label>
                        <select name="orden_id" id="edit_orden_id" class="form-select" required>
                            <option value="">Seleccione una orden</option>
                            @php
                                $listaOrdenes = ($ordenes ?? \App\Models\OrdenProduccion::latest()->take(20)->get());
                            @endphp
                            @foreach($listaOrdenes as $orden)
                                <option value="{{ $orden->id }}">{{ $orden->numero_orden }}</option>
                            @endforeach
                        </select>
                        @unless($esSuaje)
                            <small class="text-muted" id="edit_ayudaProductos" style="display:none;">Mostrando productos de la orden seleccionada.</small>
                        @endunless
                    </div>

                    @if($esSuaje)
                        {{-- SÓLO PARA SUAJE: Cantidad liberada --}}
                        <div class="mb-3">
                            <label class="form-label">Cantidad liberada</label>
                            <input type="number" name="cantidad_liberada" id="edit_cantidad_liberada" class="form-control" min="0" step="1" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cantidad de pliegos impresos</label>
                            <input type="number" name="cantidad_pliegos_impresos" id="edit_cantidad_pliegos_impresos_suaje" class="form-control" min="0" step="1">
                        </div>
                    @else
                        {{-- Producto --}}
                        <div class="mb-3">
                            <label class="form-label">Producto</label>
                            <select name="producto_id" id="edit_producto_id" class="form-select" required>
                                <option value="">Seleccione primero una orden</option>
                            </select>
                        </div>

                        {{-- Proceso --}}
                        <div class="mb-3">
                            <label class="form-label">Proceso</label>
                            <select name="proceso" id="edit_proceso" class="form-select" required>
                                @foreach(($procesos ?? []) as $p)
                                    <option value="{{ $p }}">
                                        {{ \Illuminate\Support\Str::of($p)->replace('_',' ')->title() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Realizado por --}}
                        <div class="mb-3">
                            <label class="form-label">Realizado por</label>
                            <input type="text" name="realizado_por" id="edit_realizado_por" class="form-control" required>
                        </div>

                        {{-- Cantidad de pliegos impresos --}}
                        <div class="mb-3">
                            <label class="form-label">Cantidad de pliegos impresos</label>
                            <input type="number" name="cantidad_pliegos_impresos" id="edit_cantidad_pliegos_impresos" class="form-control" min="0" step="1" placeholder="Ej: 2000">
                        </div>

                        {{-- Fecha Fin --}}
                        <div class="mb-3">
                            <label class="form-label">Fecha Fin</label>
                            <input type="datetime-local" name="fecha_fin" id="edit_fecha_fin" class="form-control">
                        </div>
                    @endif
                </div>

                <div class="modal-footer bg-light rounded-bottom-4">
                    {{-- Solo suaje: botón alerta que dispara el TOAST GLOBAL --}}
                    @if($esSuaje)
                        <button type="button" class="btn btn-danger me-auto" id="btnAlertaSuaje">
                            <i class="bi bi-exclamation-triangle"></i> Avisar al equipo
                        </button>
                    @endif

                    <button type="submit" class="btn text-white" style="background-color: #ffc107;">
                        <i class="bi bi-save"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Form oculto para el toast GLOBAL: DEBE ESTAR FUERA DEL FORM PRINCIPAL --}}
@if($esSuaje)
<form id="toastSuajeForm" action="{{ route('toasts.suaje.global.set') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="message" id="toastSuajeMessage">
</form>
@endif

<script>
(() => {
    const ES_SUAJE   = @json($esSuaje);
    const selOrden   = document.getElementById('edit_orden_id');
    const selProducto= document.getElementById('edit_producto_id'); // no existe en suaje
    const ayuda      = document.getElementById('edit_ayudaProductos'); // no existe en suaje

    async function cargarProductosDeOrdenEdit(ordenId, selectedProductoId = null) {
        if (ES_SUAJE) return;
        if (!selProducto) return;

        if (!ordenId) {
            selProducto.innerHTML = '<option value="">Seleccione primero una orden</option>';
            if (ayuda) ayuda.style.display = 'none';
            return;
        }
        selProducto.innerHTML = '<option value="">Cargando productos...</option>';
        try {
            const res   = await fetch(`/ordenes/${ordenId}/items-json`);
            const items = await res.json();

            let options = '<option value="">Seleccione un producto</option>';
            (items || []).forEach(it => {
                const pid = it.producto_id ?? it.id;
                const pnom= it.producto_nombre ?? it.nombre ?? `Producto ${pid}`;
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

    // Llamada desde el botón "Editar" en la tabla
    // Acepta 8º parámetro: cantidadPliegosSuaje (solo suaje)
    window.cargarEdicion = function(
        id, orden_id, producto_id, proceso, realizado_por, fecha_fin, cantidad, cantidadPliegosSuaje = null
    ) {
        const form = document.getElementById('formEditarAcabado');
        form.action = updateUrlTemplate.replace('__ID__', id);

        document.getElementById('edit_id').value = id ?? '';
        selOrden.value = orden_id ?? '';

        if (ES_SUAJE) {
            const inputCant  = document.getElementById('edit_cantidad_liberada');
            const inputPlieg = document.getElementById('edit_cantidad_pliegos_impresos_suaje');
            if (inputCant)  inputCant.value  = (cantidad ?? 0);
            if (inputPlieg) inputPlieg.value = (cantidadPliegosSuaje ?? '');
            return;
        }

        document.getElementById('edit_proceso').value = proceso ?? '';
        document.getElementById('edit_realizado_por').value = realizado_por ?? '';
        document.getElementById('edit_cantidad_pliegos_impresos').value = (cantidad ?? '') === null ? '' : (cantidad ?? '');
        document.getElementById('edit_fecha_fin').value = fecha_fin ?? '';

        cargarProductosDeOrdenEdit(selOrden.value, producto_id);
        selOrden.onchange = (e) => cargarProductosDeOrdenEdit(e.target.value, null);
    };

    // Botón de alerta (TOAST GLOBAL) SOLO para suaje
    if (ES_SUAJE) {
        const btnAlerta = document.getElementById('btnAlertaSuaje');
        const formToast = document.getElementById('toastSuajeForm');       // fuera del form principal
        const inputMsg  = document.getElementById('toastSuajeMessage');    // hidden input

        btnAlerta?.addEventListener('click', (e) => {
            e.preventDefault();
            // Construir mensaje con datos actuales del modal
            const ordenText = (() => {
                const opt = selOrden?.options?.[selOrden.selectedIndex];
                return opt ? (opt.textContent || opt.value || '—') : (selOrden?.value || '—');
            })();
            const cantLib = document.getElementById('edit_cantidad_liberada')?.value ?? '';
            const pliegos = document.getElementById('edit_cantidad_pliegos_impresos_suaje')?.value ?? '';

            const msg = `⚠ <b>Suaje</b> – Orden ${ordenText}: liberada ${cantLib || '0'}${pliegos ? ` • pliegos ${pliegos}` : ''}`;

            if (formToast && inputMsg) {
                inputMsg.value = msg;
                formToast.submit(); // -> route('toasts.suaje.global.set') (aparece a TODOS)
            } else {
                console.warn('toastSuajeForm o toastSuajeMessage no existen en el DOM.');
            }
        });
    }
})();
</script>
