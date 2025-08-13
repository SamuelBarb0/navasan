<div class="modal fade" id="modalEditar{{ $etiqueta->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $etiqueta->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <form method="POST"
              action="{{ route('inventario-etiquetas.update', $etiqueta->id) }}"
              class="w-100"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="orden_id" value="{{ $etiqueta->orden_id }}">

            <div class="modal-content shadow-lg rounded-4 border-0">
                <div class="modal-header text-white rounded-top-4" style="background-color: #16509D;">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i> Editar Inventario (#{{ $etiqueta->id }})
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body px-4 py-3">

                    {{-- Cliente (opcional) --}}
                    <div class="mb-3">
                        <label class="form-label">Cliente (opcional)</label>
                        <select name="cliente_id" id="clienteSelectEdit{{ $etiqueta->id }}" class="form-select rounded-3">
                            <option value="">Sin cliente</option>
                            @foreach(($clientes ?? []) as $c)
                                <option value="{{ $c->id }}" @selected($etiqueta->cliente_id == $c->id)>{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Producto --}}
                    <div class="mb-3">
                        <label class="form-label">Producto</label>
                        @if($etiqueta->orden_id)
                            <select name="item_orden_id" id="productoSelect{{ $etiqueta->id }}" class="form-select rounded-3" required>
                                <option value="">Cargando productos...</option>
                            </select>
                        @else
                            <select name="producto_id" id="productoSelect{{ $etiqueta->id }}" class="form-select rounded-3" required>
                                <option value="">Cargando productos...</option>
                            </select>
                        @endif
                    </div>

                    {{-- Cantidad --}}
                    <div class="mb-3">
                        <label class="form-label">Cantidad</label>
                        <input type="number" name="cantidad" class="form-control rounded-3" value="{{ $etiqueta->cantidad }}" required min="1">
                    </div>

                    {{-- Fecha --}}
                    <div class="mb-3">
                        <label class="form-label">Fecha programada</label>
                        <input type="date" name="fecha_programada" class="form-control rounded-3" value="{{ $etiqueta->fecha_programada }}">
                    </div>

                    {{-- Observaciones --}}
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control rounded-3" rows="2">{{ $etiqueta->observaciones }}</textarea>
                    </div>

                    {{-- Estado --}}
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select rounded-3" required>
                            <option value="pendiente" {{ $etiqueta->estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="stock" {{ $etiqueta->estado === 'stock' ? 'selected' : '' }}>Stock</option>
                            <option value="liberado" {{ $etiqueta->estado === 'liberado' ? 'selected' : '' }}>Liberado</option>
                        </select>
                    </div>

                    {{-- Imagen única + preview --}}
                    @php
                        $placeholder = asset('images/no-image.png');
                        $preview = ($etiqueta->imagen_url ?? null)
                            ?? (optional($etiqueta->producto)->imagen_url ?? $placeholder);
                    @endphp
                    <div class="mb-2">
                        <label class="form-label">Imagen (opcional, 1)</label>
                        <input type="file" name="imagen" id="imagen{{ $etiqueta->id }}" class="form-control" accept="image/*">
                        <div class="mt-2 d-flex align-items-center gap-3">
                            <img id="previewImg{{ $etiqueta->id }}" src="{{ $preview }}" alt="Preview" style="width:140px;height:140px;object-fit:cover;border:1px solid #eee;border-radius:8px;">
                            <div>
                                @if(!empty($etiqueta->imagen_url))
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="eliminar_imagen" value="1" id="elimImg{{ $etiqueta->id }}">
                                        <label class="form-check-label" for="elimImg{{ $etiqueta->id }}">Eliminar imagen actual</label>
                                    </div>
                                @endif
                                <small class="text-muted d-block mt-1">Formatos: JPG/PNG, máx. 4MB.</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">
                </div>

                <div class="modal-footer bg-light border-top-0 rounded-bottom-4 px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ordenId            = {{ $etiqueta->orden_id ?? 'null' }};
    const selectProd         = document.getElementById('productoSelect{{ $etiqueta->id }}');
    const selectedItemId     = {{ $etiqueta->item_orden_id ?? 'null' }};
    const selectedProductoId = {{ $etiqueta->producto_id ?? 'null' }};
    const fileInput          = document.getElementById('imagen{{ $etiqueta->id }}');
    const previewImg         = document.getElementById('previewImg{{ $etiqueta->id }}');
    const placeholder        = @json(asset('images/no-image.png'));

    const clienteSelect      = document.getElementById('clienteSelectEdit{{ $etiqueta->id }}');
    const clienteIdInicial   = {{ $etiqueta->cliente_id ?? 'null' }};

    // Helper: construir opciones y seleccionar valor
    function renderOptions(items, selectedId) {
        let options = '<option value="">Seleccione un producto</option>';
        items.forEach(it => {
            const sel   = (String(it.id) === String(selectedId)) ? 'selected' : '';
            const dImg  = it.imagen_url ? `data-img="${it.imagen_url}"` : '';
            const nombre= it.nombre ?? it.text ?? `#${it.id}`;
            options += `<option value="${it.id}" ${sel} ${dImg}>${nombre}</option>`;
        });
        selectProd.innerHTML = options;
    }

    // Cargar por ORDEN
    function cargarPorOrden(idOrden) {
        selectProd.innerHTML = '<option value="">Cargando productos...</option>';
        fetch(`/ordenes/${idOrden}/items-json`)
            .then(r => r.json())
            .then(items => {
                renderOptions(items, selectedItemId);
            })
            .catch(() => {
                selectProd.innerHTML = '<option value="">Error al cargar productos</option>';
            });
    }

    // Cargar por CLIENTE
    function cargarPorCliente(idCliente) {
        selectProd.innerHTML = '<option value="">Cargando productos...</option>';
        fetch(`/clientes/${idCliente}/productos-json`)
            .then(r => r.json())
            .then(productos => {
                renderOptions(productos, selectedProductoId);
                // Si no hay archivo y no hay imagen actual, usar imagen del producto
                if (!{{ $etiqueta->imagen_url ? 'true' : 'false' }}) {
                    const opt = selectProd.options[selectProd.selectedIndex];
                    const url = opt?.getAttribute('data-img');
                    if (url) previewImg.src = url;
                }
            })
            .catch(() => {
                selectProd.innerHTML = '<option value="">Error al cargar productos</option>';
            });
    }

    // Cargar TODOS
    function cargarTodos() {
        selectProd.innerHTML = '<option value="">Cargando productos...</option>';
        fetch(`/productos/todos-json`)
            .then(r => r.json())
            .then(productos => {
                renderOptions(productos, selectedProductoId);
                if (!{{ $etiqueta->imagen_url ? 'true' : 'false' }}) {
                    const opt = selectProd.options[selectProd.selectedIndex];
                    const url = opt?.getAttribute('data-img');
                    if (url) previewImg.src = url;
                }
            })
            .catch(() => {
                selectProd.innerHTML = '<option value="">Error al cargar productos</option>';
            });
    }

    // Lógica principal de carga
    function cargarFuenteProductos() {
        if (ordenId) {
            cargarPorOrden(ordenId);
        } else if (clienteSelect?.value) {
            cargarPorCliente(clienteSelect.value);
        } else {
            cargarTodos();
        }
    }

    // Preview según producto (si NO hay archivo)
    selectProd?.addEventListener('change', () => {
        if (fileInput?.files?.length) return;
        const opt = selectProd.options[selectProd.selectedIndex];
        const url = opt?.getAttribute('data-img');
        previewImg && (previewImg.src = url || placeholder);
    });

    // Preview según archivo (tiene prioridad)
    fileInput?.addEventListener('change', () => {
        if (!fileInput.files.length) {
            const opt = selectProd?.options?.[selectProd.selectedIndex];
            const url = opt?.getAttribute('data-img');
            previewImg && (previewImg.src = url || placeholder);
            return;
        }
        const reader = new FileReader();
        reader.onload = e => { previewImg && (previewImg.src = e.target.result); };
        reader.readAsDataURL(fileInput.files[0]);
    });

    // Si cambia el cliente (y no hay orden), recargar productos del cliente
    clienteSelect?.addEventListener('change', () => {
        if (ordenId) return; // si hay orden, el producto depende de la orden, no del cliente
        if (clienteSelect.value) {
            cargarPorCliente(clienteSelect.value);
        } else {
            cargarTodos();
        }
    });

    // Carga inicial
    // Si ya viene con cliente seleccionado (o no), seguirá la prioridad: orden > cliente > todos
    if (clienteSelect && clienteIdInicial && !ordenId) {
        clienteSelect.value = String(clienteIdInicial);
    }
    cargarFuenteProductos();
});
</script>
